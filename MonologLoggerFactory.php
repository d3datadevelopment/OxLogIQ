<?php

declare(strict_types=1);

namespace D3\ShopLogger;

use D3\LoggerFactory\LoggerFactory;
use D3\ShopLogger\Processors\SessionIdProcessor;
use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Validator\LoggerConfigurationValidatorInterface;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory implements LoggerFactoryInterface
{
    /** @var MonologConfiguration */
    private $configuration;

    public function __construct(
        MonologConfigurationInterface $configuration,
        LoggerConfigurationValidatorInterface $configurationValidator
    ) {
        $configurationValidator->validate($configuration);

        $this->configuration = $configuration;
    }

    /**
     * @throws Exception
     */
    public function create(): LoggerInterface
    {
        $factory = LoggerFactory::create();

        $fileHandlerOption = $factory->addFileHandler(
            $this->configuration->getLogFilePath(),
            Logger::toMonologLevel($this->configuration->getLogLevel()),
            $this->configuration->getRemainingFiles()
        );
        $fileHandlerOption->getHandler()->setFormatter($this->getFormatter());
        $fileHandlerOption->setBuffering();

        if ($this->configuration->hasNotificationMailAddress()) {
            $to       = [ $this->configuration->getNotificationMailAddress() ];
            $subject  = 'Shop Log Notification';
            $from     = $this->configuration->getNotificationMailAddress();
            $logLevel = Logger::ERROR;
            $factory->addMailHandler( $to, $subject, $from, $logLevel )->setBuffering();
        }

        $factory->addUidProcessor();
        $factory->addOtherProcessor(
            new IntrospectionProcessor(Logger::ERROR, ['Internal\\Framework\\Logger\\'])
        );
        $factory->addOtherProcessor(new SessionIdProcessor());

        return $factory->build($this->configuration->getLoggerName());
    }

    private function getFormatter(): FormatterInterface
    {
        $formatter = new LineFormatter();
        $formatter->includeStacktraces();

        return $formatter;
    }
}