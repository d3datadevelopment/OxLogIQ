<?php

declare(strict_types=1);

namespace D3\ShopLogger;

use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory implements LoggerFactoryInterface
{
    public function __construct(private LoggerFactoryInterface $innerFactory)
    {
    }

    public function create(): LoggerInterface
    {
        $logger = $this->innerFactory->create();

        // Hier kannst du den Logger verändern, z. B. zusätzlichen Handler oder Prozessor anhängen:
        // $logger->pushHandler(...);

        return $logger;
    }

//    public function create()
//    {
//        $handler = $this->getHandler();
//
//        $logger = new Logger($this->configuration->getLoggerName());
//        $logger->pushHandler($handler);
//
//        return $logger;
//    }

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