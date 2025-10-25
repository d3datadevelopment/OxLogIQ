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

namespace D3\OxLogiQ;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

class MonologConfiguration implements MonologConfigurationInterface
{
    /**
     * @param \OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfiguration $innerConfig
     * @param \OxidEsales\EshopCommunity\Core\Config $config
     */
    public function __construct(
        protected MonologConfigurationInterface $innerConfig,
        protected Config $config,
        protected ?int $remainingFiles,
        protected ?string $notificationMailAddress
    ) {}

    public function getLoggerName(): string
    {
        return implode(
            '|',
            [
                $this->innerConfig->getLoggerName().
                'shp-'.$this->config->getActiveShop()->getId().
                $this->getContext()
            ]
        );
    }

    protected function getContext(): string
    {
        return isAdmin()?'backend':'frontend';
    }

    public function getLogFilePath(): string
    {
        return $this->innerConfig->getLogFilePath();
    }

    public function getLogLevel(): string
    {
        return $this->innerConfig->getLogLevel();
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