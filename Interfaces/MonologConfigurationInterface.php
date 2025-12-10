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

namespace D3\OxLogIQ\Interfaces;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

interface MonologConfigurationInterface
{
    public function getRetentionDays(): ?int;

    public function useAlertMail(): bool;

    public function hasAlertMailRecipient(): bool;

    public function getAlertMailRecipients(): ?array;

    public function getAlertMailLevel(): string;

    public function getAlertMailSubject(): string;

    public function getAlertMailFrom(): ?string;

    public function hasSentryDsn(): bool;

    public function getSentryDsn(): ?string;

    public function getSentryOptions(): iterable;

    public function getRelease(): string;

    public function hasHttpApiEndpoint(): bool;

    public function getHttpApiEndpoint(): ?string;

    public function getHttpApiKey(): string;

    public function getHttpClient(): ClientInterface;

    public function getHttpRequestFactory(): RequestFactoryInterface;

    public function getHttpStreamFactory(): StreamFactoryInterface;
}
