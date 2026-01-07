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

interface MonologConfigurationInterface
{
    public function getRetentionDays(): ?int;

    public function useAlertMail(): bool;

    public function hasAlertMailRecipient(): bool;

    /**
     * @return string[]|null
     */
    public function getAlertMailRecipients(): ?array;

    public function getAlertMailLevel(): string;

    public function getAlertMailSubject(): string;

    public function getAlertMailFrom(): ?string;

    public function getRelease(): string;
}
