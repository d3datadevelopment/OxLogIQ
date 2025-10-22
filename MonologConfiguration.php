<?php

declare(strict_types=1);

namespace D3\Shoplogger;

use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

class MonologConfiguration implements MonologConfigurationInterface
{
    public function __construct(
        private MonologConfigurationInterface $innerConfig,
        private string $logFilePath,
        private string $logLevel
    ) {}

    public function getLoggerName(): string
    {
        return $this->innerConfig->getLoggerName();
        return '[D3 Shoplogger] ' . $this->innerConfig->getLoggerName();
    }

    public function getLogFilePath(): string
    {
        // z. B. anderer Pfad
        return $this->logFilePath ?: $this->innerConfig->getLogFilePath();
    }

    public function getLogLevel(): string
    {
        return $this->logLevel ?: $this->innerConfig->getLogLevel();
        // z. B. fest auf DEBUG zwingen
        return 'debug';
    }

    public function getRemainingFiles(): int
    {
        return 5;
    }

    public function getNotificationMailAddress(): ?string
    {
        return 'alert@ds.data-develop.de';
    }
}