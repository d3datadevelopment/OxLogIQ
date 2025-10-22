<?php

declare(strict_types=1);

namespace D3\ShopLogger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Validator\LoggerConfigurationValidatorInterface;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var MonologConfigurationInterface $configuration
     */
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
     * @return LoggerInterface
     */
    public function create()
    {
        $handler = $this->getFileHandler();

        $logger = new Logger($this->configuration->getLoggerName());
        $logger->pushHandler($handler);
        $logger->pushHandler($this->getMailHandler());

        return $logger;
    }

    /**
     * @return HandlerInterface
     */
    private function getFileHandler()
    {
        $handler = new RotatingFileHandler(
            $this->configuration->getLogFilePath(),
            $this->configuration->getRemainingFiles(),
            $this->configuration->getLogLevel(),
        );

//        $handler = new StreamHandler(
//            $this->configuration->getLogFilePath(),
//            $this->configuration->getLogLevel()
//        );

        $formatter = $this->getFormatter();
        $handler->setFormatter($formatter);

        return $handler;
    }

    /**
     * @return HandlerInterface
     */
    private function getMailHandler()
    {
        $isHtml = true;
        $to = 'ox73@ds.data-develop.de';
        $subject = 'shop logger';
        $from = 'ox73@ds.data-develop.de';
        $logLevel = Logger::NOTICE;

        $handler = new NativeMailerHandler($to, $subject, $from, $logLevel);

        if ($isHtml) {
            $handler
                ->setContentType('text/html')
                ->setEncoding('iso-8859-1')
                ->setFormatter(new HtmlFormatter());
        } else {
            $handler->setFormatter(
                new LineFormatter()
            );
        }

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