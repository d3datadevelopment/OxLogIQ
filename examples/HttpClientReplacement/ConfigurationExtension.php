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

namespace D3\LoggerExtension;

use D3\OxLogIQ\Interfaces\MonologConfigurationInterface as OxLogIQConfigurationInterfaceAlias;
use D3\OxLogIQ\MonologLoggerFactory;
use Nimbly\Shuttle\Shuttle;
use Nyholm\Psr7\Factory\Psr17Factory;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ConfigurationExtension implements MonologConfigurationInterface, OxLogIQConfigurationInterfaceAlias
{
    /**
     * @param MonologLoggerFactory $innerConfig
     */
    public function __construct(
        protected MonologConfigurationInterface $innerConfiguration
    ) {
    }

    public function getLoggerName()
    {
        return $this->innerConfiguration->getLoggerName();
    }

    public function getLogFilePath()
    {
        return $this->innerConfiguration->getLogFilePath();
    }

    public function getLogLevel()
    {
        return $this->innerConfiguration->getLogLevel();
    }

    public function getRetentionDays(): ?int
    {
        return $this->innerConfiguration->getRetentionDays();
    }

    public function useAlertMail(): bool
    {
        return $this->innerConfiguration->useAlertMail();
    }

    public function hasAlertMailRecipient(): bool
    {
        return $this->innerConfiguration->hasAlertMailRecipient();
    }

    public function getAlertMailRecipients(): ?array
    {
        return $this->innerConfiguration->getAlertMailRecipients();
    }

    public function getAlertMailLevel(): string
    {
        return $this->innerConfiguration->getAlertMailLevel();
    }

    public function getAlertMailSubject(): string
    {
        return $this->innerConfiguration->getAlertMailSubject();
    }

    public function getAlertMailFrom(): ?string
    {
        return $this->innerConfiguration->getAlertMailFrom();
    }

    public function hasSentryDsn(): bool
    {
        return $this->innerConfiguration->hasSentryDsn();
    }

    public function getSentryDsn(): ?string
    {
        return $this->innerConfiguration->getSentryDsn();
    }

    public function getSentryOptions(): iterable
    {
        return $this->innerConfiguration->getSentryOptions();
    }

    public function getRelease(): string
    {
        return $this->innerConfiguration->getRelease();
    }

    public function hasHttpApiEndpoint(): bool
    {
        return $this->innerConfiguration->hasHttpApiEndpoint();
    }

    public function getHttpApiEndpoint(): ?string
    {
        return $this->innerConfiguration->getHttpApiEndpoint();
    }

    public function getHttpApiKey(): string
    {
        return $this->innerConfiguration->getHttpApiKey();
    }

    public function getHttpClient(): ClientInterface
    {
        // configure it according to your needs.
        return new Shuttle();
    }

    public function getHttpRequestFactory(): RequestFactoryInterface
    {
        return new Psr17Factory();
    }

    public function getHttpStreamFactory(): StreamFactoryInterface
    {
        return new Psr17Factory();
    }
}
