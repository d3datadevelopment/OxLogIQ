<?php

/**
 * Copyright (c) D3 Data Development (Inh. Thomas Dartsch)
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\OxLogIQ;

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\Handlers\HttpApiHandler;
use D3\OxLogIQ\Processors\SentryExceptionProcessor;
use D3\OxLogIQ\Processors\SessionIdProcessor;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Validator\LoggerConfigurationValidatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use Sentry\Monolog\BreadcrumbHandler;
use Sentry\Monolog\Handler;
use Sentry\SentrySdk;

use function Sentry\init;

class MonologLoggerFactory implements LoggerFactoryInterface
{
    /**
     * @param MonologConfiguration         $configuration
     */
    public function __construct(
        protected MonologConfigurationInterface $configuration,
        LoggerConfigurationValidatorInterface $configurationValidator,
        protected LoggerFactory $loggerFactory
    ) {
        $configurationValidator->validate($configuration);
    }

    public function getFactory(): LoggerFactory
    {
        $factory = $this->loggerFactory;

        $this->addFileHandler($factory);
        $this->addMailHandler($factory);
        $this->addSentryHandler($factory);
        $this->addHttpApiHandler($factory);
        $this->addProcessors($factory);

        return $factory;
    }

    /**
     * @throws Exception
     */
    public function create(): LoggerInterface
    {
        return $this->getFactory()->build($this->configuration->getLoggerName());
    }

    protected function addFileHandler(LoggerFactory $factory): void
    {
        try {
            $fileHandlerOption = $factory->addFileHandler(
                $this->configuration->getLogFilePath(),
                Logger::toMonologLevel($this->configuration->getLogLevel()),
                $this->configuration->getRetentionDays()
            );

            $fileHandlerOption->getHandler()->setFormatter($this->getFormatter());
            $fileHandlerOption->setBuffering();
        } catch (Exception $exception) {
            error_log('OxLogIQ: '.$exception->getMessage());
        }
    }

    protected function getFormatter(): FormatterInterface
    {
        $formatter = new LineFormatter();
        $formatter->includeStacktraces();

        return $formatter;
    }

    protected function addMailHandler(LoggerFactory $factory): void
    {
        if ($this->configuration->useAlertMail()) {
            try {
                /** @var Shop $shop */
                $shop = Registry::getConfig()->getActiveShop();
                $to       = (array) $this->configuration->getAlertMailRecipients();
                $subject  = sprintf(
                    '%1$s - %2$s',
                    $shop->getFieldData('oxname'),
                    $this->configuration->getAlertMailSubject()
                );
                $from     = (string) (
                    $this->configuration->getAlertMailFrom() ?? $shop->getFieldData('oxinfoemail')
                );
                $logLevel = (int) Logger::toMonologLevel($this->configuration->getAlertMailLevel());
                $factory->addMailHandler($to, $subject, $from, $logLevel)->setBuffering();
            } catch (Exception $exception) {
                error_log('OxLogIQ: '.$exception->getMessage());
            }
        }
    }

    protected function addSentryHandler(LoggerFactory $factory): void
    {
        if ($this->configuration->hasSentryDsn()) {
            try {
                init($this->configuration->getSentryOptions());

                $factory->addOtherHandler(
                    (new BreadcrumbHandler(
                        SentrySdk::getCurrentHub(),
                        Logger::INFO
                    ))
                )->setLogOnErrorOnly(
                    $this->configuration->getLogLevel()
                );

                $factory->addOtherHandler(
                    (new Handler(
                        SentrySdk::getCurrentHub(),
                        Logger::toMonologLevel($this->configuration->getLogLevel())
                    ))
                        ->pushProcessor(new SentryExceptionProcessor())
                )->setBuffering();
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $exception) {
                error_log('OxLogIQ: '.$exception->getMessage());
            }
        }
    }

    protected function addHttpApiHandler(LoggerFactory $factory): void
    {
        if ($this->configuration->hasHttpApiEndpoint()) {
            try {
                $factory->addOtherHandler(
                    (new HttpApiHandler(
                        $this->configuration->getHttpApiEndpoint(),
                        $this->configuration->getHttpApiKey(),
                        Logger::toMonologLevel($this->configuration->getLogLevel()),
                        $this->configuration->getHttpClient(),
                        $this->configuration->getHttpRequestFactory(),
                        $this->configuration->getHttpStreamFactory(),
                    ))
                )->setBuffering();
            } catch (InvalidArgumentException $exception) {
                error_log('OxLogIQ: '.$exception->getMessage());
            }
        }
    }

    /**
     * @param LoggerFactory $factory
     *
     * @return void
     */
    protected function addProcessors(LoggerFactory $factory): void
    {
        try {
            $factory->addUidProcessor();
            $factory->addOtherProcessor(
                new IntrospectionProcessor(Logger::ERROR, [
                    'Internal\\Framework\\Logger\\',
                ])
            );
            $factory->addOtherProcessor(
                new SessionIdProcessor(Registry::getSession())
            );
        } catch (InvalidArgumentException $exception) {
            error_log('OxLogIQ: '.$exception->getMessage());
        }
    }
}
