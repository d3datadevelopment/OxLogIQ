<?php

declare(strict_types=1);

namespace D3\ShopLogger;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

class MonologConfiguration implements MonologConfigurationInterface
{
    /**
     * @param \OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfiguration $innerConfig
     */
    public function __construct(
        private MonologConfigurationInterface $innerConfig,
        private string $logFilePath,
        private string $logLevel,
        private ?int $remainingFiles,
        private ?string $notificationMailAddress
    ) {}

    public function __call(string $name, array $arguments)
    {
        return $this->innerConfig->$name(...$arguments);
    }

    public function getLoggerName(): string
    {
        return $this->innerConfig->getLoggerName().
            '|shp-'.Registry::getConfig()->getActiveShop()->getId().
            '|'.(isAdmin()?'backend':'frontend');
    }

    public function getLogFilePath(): string
    {
        return $this->logFilePath ?: $this->innerConfig->getLogFilePath();
    }

    public function getLogLevel(): string
    {
        return $this->logLevel ?: $this->innerConfig->getLogLevel();
    }

    public function getRemainingFiles(): ?int
    {
        return $this->remainingFiles;
    }

    public function hasNotificationMailAddress(): bool
    {
        return isset($this->notificationMailAddress) && strlen($this->notificationMailAddress);
    }

    public function getNotificationMailAddress(): ?string
    {
        return $this->notificationMailAddress;
    }
}