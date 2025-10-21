<?php

namespace D3\Shoplogger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\MonologLoggerFactory as OxidLoggerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Validator\LoggerConfigurationValidatorInterface;

class MonologLoggerFactory extends OxidLoggerFactory
{
    /**
     * @var MonologConfigurationInterface $configuration
     */
    private $configuration;

    public function __construct(
        MonologConfigurationInterface $configuration,
        LoggerConfigurationValidatorInterface $configurationValidator
    ) {
        $configurationValidator->validate($configuration);

        $this->configuration = $configuration;
    }

    public function create()
    {
        $handler = $this->getHandler();

        $logger = new Logger($this->configuration->getLoggerName());
        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * @return HandlerInterface
     */
    private function getHandler()
    {
        $handler = new StreamHandler(
            $this->configuration->getLogFilePath(),
            $this->configuration->getLogLevel()
        );

        $formatter = $this->getFormatter();
        $handler->setFormatter($formatter);

        return $handler;
    }

    /**
     * @return FormatterInterface
     */
    private function getFormatter()
    {
        $formatter = new LineFormatter();
        $formatter->includeStacktraces(true);

        return $formatter;
    }
}