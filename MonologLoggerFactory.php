<?php

declare(strict_types=1);

namespace D3\OxLogiQ;

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogiQ\Processors\SessionIdProcessor;
use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Validator\LoggerConfigurationValidatorInterface;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory implements LoggerFactoryInterface
{
    /** @var MonologConfiguration */
    protected $configuration;

    public function __construct(
        MonologConfigurationInterface $configuration,
        LoggerConfigurationValidatorInterface $configurationValidator,
        protected LoggerFactory $loggerFactory
    ) {
        $configurationValidator->validate($configuration);

        $this->configuration = $configuration;
    }

    /**
     * @throws Exception
     */
    public function create(): LoggerInterface
    {
        $factory = $this->loggerFactory;

        $this->addFileHandler($factory);
        $this->addMailHandler($factory);
        $this->addProcessors($factory);

        return $factory->build($this->configuration->getLoggerName());
    }

    /**
     * @throws Exception
     */
    protected function addFileHandler(LoggerFactory $factory): void
    {
        $fileHandlerOption = $factory->addFileHandler(
            $this->configuration->getLogFilePath(),
            Logger::toMonologLevel($this->configuration->getLogLevel()),
            $this->configuration->getRemainingFiles()
        );

        $fileHandlerOption->getHandler()->setFormatter($this->getFormatter());
        $fileHandlerOption->setBuffering();
    }

    protected function getFormatter(): FormatterInterface
    {
        $formatter = new LineFormatter();
        $formatter->includeStacktraces();

        return $formatter;
    }

    /**
     * @param LoggerFactory $factory
     *
     * @return void
     */
    protected function addMailHandler(LoggerFactory $factory): void
    {
        if ($this->configuration->hasNotificationMailRecipient()) {
            $shop = Registry::getConfig()->getActiveShop();
            $to       = $this->configuration->getNotificationMailRecipients();
            $subject  = $shop->getFieldData( 'oxname' ).' '.$this->configuration->getNotificationMailSubject();
            $from     = $shop->getFieldData( 'oxinfoemail' );
            $logLevel = Logger::toMonologLevel($this->configuration->getNotificationMailLevel());
            $factory->addMailHandler($to, $subject, $from, $logLevel)->setBuffering();
        }
    }

    /**
     * @param LoggerFactory $factory
     *
     * @return void
     */
    protected function addProcessors(LoggerFactory $factory): void
    {
        $factory->addUidProcessor();
        $factory->addOtherProcessor(
            new IntrospectionProcessor(Logger::ERROR, ['Internal\\Framework\\Logger\\'])
        );
        $factory->addOtherProcessor(
            new SessionIdProcessor( Registry::getSession())
        );
    }
}