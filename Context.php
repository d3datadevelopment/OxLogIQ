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

namespace D3\OxLogIQ;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\Context as OxidContext;
use OxidEsales\Facts\Config\ConfigFile as FactsConfigFile;

class Context extends OxidContext
{
    public const CONFIGVAR_RETENTIONDAYS    = 'oxlogiq_retentionDays';
    public const CONFIGVAR_MAILTOGGLE       = 'oxlogiq_mailAlert';
    public const CONFIGVAR_MAILRECIPIENTS   = 'oxlogiq_mailRecipients';
    public const CONFIGVAR_MAILLEVEL        = 'oxlogiq_mailLogLevel';
    public const CONFIGVAR_MAILSUBJECT      = 'oxlogiq_mailSubject';
    public const CONFIGVAR_MAILFROM         = 'oxlogiq_mailFrom';
    public const CONFIGVAR_SENTRY_DSN       = 'oxlogiq_sentryDsn';
    public const CONFIGVAR_HTTPAPI_ENDPOINT = 'oxlogiq_httpApiEndpoint';
    public const CONFIGVAR_HTTPAPI_KEY      = 'oxlogiq_httpApiKey';

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

    public function useAlertMail(): bool
    {
        return isset($_ENV[self::CONFIGVAR_MAILTOGGLE]) ?
            (bool) $_ENV[self::CONFIGVAR_MAILTOGGLE] ??
            (bool) $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILTOGGLE) :
            (bool) $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILTOGGLE);
    }

    /**
     * @return string[]|null
     */
    public function getAlertMailRecipients(): ?array
    {
        $recipients = $_ENV[self::CONFIGVAR_MAILRECIPIENTS] ??
            $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILRECIPIENTS) ??
            $this->getFactsConfigFile()->getVar('sAdminEmail');


        $recipients = is_string($recipients) ? [$recipients] : $recipients;
        $recipients = is_array($recipients) ? array_filter($recipients) : $recipients;
        return is_array($recipients) ?
            !count($recipients) ? null : $recipients :
            $recipients;
    }

    public function getAlertMailLevel(): string
    {
        return $_ENV[self::CONFIGVAR_MAILLEVEL] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILLEVEL) ??
               'ERROR';
    }

    public function getAlertMailSubject(): string
    {
        return $_ENV[self::CONFIGVAR_MAILSUBJECT] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILSUBJECT) ??
               'Shop Log Alert';
    }

    public function getAlertMailFrom(): ?string
    {
        return $_ENV[self::CONFIGVAR_MAILFROM] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_MAILFROM);
    }

    public function getSentryDsn(): ?string
    {
        return $_ENV[self::CONFIGVAR_SENTRY_DSN] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_SENTRY_DSN);
    }

    public function getHttpApiEndpoint(): ?string
    {
        return $_ENV[self::CONFIGVAR_HTTPAPI_ENDPOINT] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_HTTPAPI_ENDPOINT);
    }

    public function getHttpApiKey(): ?string
    {
        return $_ENV[self::CONFIGVAR_HTTPAPI_KEY] ??
               $this->getFactsConfigFile()->getVar(self::CONFIGVAR_HTTPAPI_KEY);
    }
}
