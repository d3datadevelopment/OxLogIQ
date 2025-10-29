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

use OxidEsales\EshopCommunity\Internal\Transition\Utility\Context as OxidContext;
use OxidEsales\Facts\Config\ConfigFile as FactsConfigFile;

class Context extends OxidContext
{
    public const CONFIGVAR_RETENTIONDAYS    = 'oxlogiq_retentionDays';
    public const CONFIGVAR_MAILRECIPIENTS   = 'oxlogiq_mailRecipients';
    public const CONFIGVAR_MAILLEVEL        = 'oxlogiq_mailLogLevel';
    public const CONFIGVAR_MAILSUBJECT      = 'oxlogiq_mailSubject';

    /**
     * @var FactsConfigFile
     */
    private $factsConfigFile;

    /**
     * @return FactsConfigFile
     */
    protected function getFactsConfigFile(): FactsConfigFile
    {
        if (!is_a($this->factsConfigFile, FactsConfigFile::class)) {
            $this->factsConfigFile = new FactsConfigFile();
        }

        return $this->factsConfigFile;
    }

    public function getRetentionDays(): ?int
    {
        $retention = $_ENV[self::CONFIGVAR_RETENTIONDAYS] ??
                     $this->getFactsConfigFile()->getVar(self::CONFIGVAR_RETENTIONDAYS);

        return !is_int($retention) ? null : $retention;
    }

    public function getNotificationMailRecipients(): ?array
    {
        $recipients = $_ENV[self::CONFIGVAR_MAILRECIPIENTS] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILRECIPIENTS);

        return is_string($recipients) ? [$recipients] : $recipients;
    }

    public function getNotificationMailLevel(): string
    {
        return $_ENV[self::CONFIGVAR_MAILLEVEL] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILLEVEL) ??
               'ERROR';
    }

    public function getNotificationMailSubject(): string
    {
        return $_ENV[self::CONFIGVAR_MAILSUBJECT] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILSUBJECT) ??
               'Shop Log Notification';
    }
}