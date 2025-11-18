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

use DateTimeImmutable;
use InvalidArgumentException;
use Monolog\Logger;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\FileException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use Sentry\Event as SentryEvent;
use Sentry\Tracing\SamplingContext;

class MonologConfiguration implements MonologConfigurationInterface
{
    /**
     * @param \OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfiguration $innerConfig
     * @param \OxidEsales\EshopCommunity\Core\Config $config
     * @param Context $context
     */
    public function __construct(
        protected MonologConfigurationInterface $innerConfig,
        protected Config $config,
        protected ContextInterface $context
    ) {
    }

    public function getLoggerName(): string
    {
        /** @var Shop $shop */
        $shop = $this->config->getActiveShop();

        return implode(
            '|',
            [
                $this->innerConfig->getLoggerName(),
                'shp-'.$shop->getId(),
                $this->getContext(),
            ]
        );
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getContext(): string
    {
        return isAdmin() ? 'backend' : 'frontend';  // @phpstan-ignore function.notFound
    }

    public function getLogFilePath(): string
    {
        return $this->innerConfig->getLogFilePath();
    }

    public function getLogLevel(): string
    {
        // is already validated
        return $this->innerConfig->getLogLevel();
    }

    public function getRetentionDays(): ?int
    {
        return $this->context->getRetentionDays();
    }

    public function hasAlertMailRecipient(): bool
    {
        $recipients = $this->getAlertMailRecipients();

        return is_array($recipients) && count($recipients) > 0;
    }

    /**
     * @return string[]|null
     */
    public function getAlertMailRecipients(): ?array
    {
        return $this->context->getAlertMailRecipients();
    }

    public function getAlertMailLevel(): string
    {
        $level = strtoupper($this->context->getAlertMailLevel());

        if (!in_array($level, array_keys(Logger::getLevels()), true)) {
            throw new InvalidArgumentException(
                'Mail alerting level must be one of '.implode(', ', array_keys(Logger::getLevels()))
            );
        }

        return $level;
    }

    public function getAlertMailSubject(): string
    {
        return $this->context->getAlertMailSubject();
    }

    public function getAlertMailFrom(): ?string
    {
        return $this->context->getAlertMailFrom();
    }

    public function hasSentryDsn(): bool
    {
        $dsn = $this->context->getSentryDsn();

        return isset($dsn) &&
            is_string($dsn) && strlen(trim($dsn));
    }

    public function getSentryDsn(): ?string
    {
        return $this->context->getSentryDsn();
    }

    public function getSentryOptions(): iterable
    {
        return [
            'dsn' => $this->getSentryDsn(),
            'enable_logs' => true,
            'traces_sampler' => $this->getSentryTracesSampleRate(),
            'environment' => Registry::getConfig()->getActiveShop()->isProductiveMode() ?
                'production' :
                'development',
            'release' => $this->getRelease(),
            'before_send' => $this->beforeSendToSentry(),
            'prefixes' => [
                realpath(
                    rtrim(Registry::getConfig()->getConfigParam('sShopDir'), DIRECTORY_SEPARATOR).
                    DIRECTORY_SEPARATOR.'..').DIRECTORY_SEPARATOR,
            ]
        ];
    }

    protected function getRelease(): string
    {
        try {
            $path = Registry::getConfig()->getConfigParam('sShopDir') . '../composer.lock';
            $realPath = realpath($path);

            if (!$realPath) {
                throw new FileException(sprintf('composer.lock file not found in path %s', $path));
            }

            return (new DateTimeImmutable())->setTimestamp(filemtime($realPath))->format('Y-m-d_H:i:s');
        } catch (StandardException) {
            return '';
        }
    }

    protected function getSentryTracesSampleRate(): callable
    {
        return function (SamplingContext $context): float {
            if ($context->getParentSampled()) {
                return 1.0;
            }
            return 0.25;
        };
    }

    protected function beforeSendToSentry(): callable
    {
        return function (SentryEvent $event): ?SentryEvent {
            return $event;
        };
    }

    public function hasSentryDsn(): bool
    {
        $dsn = $this->context->getSentryDsn();

        return isset($dsn) &&
            is_string($dsn) && strlen(trim($dsn));
    }

    public function getSentryDsn(): ?string
    {
        return $this->context->getSentryDsn();
    }

    public function getSentryOptions(): iterable
    {
        return [
            'dsn' => $this->getSentryDsn(),
            'enable_logs' => true,
            'traces_sampler' => $this->getSentryTracesSampleRate(),
            'environment' => Registry::getConfig()->getActiveShop()->isProductiveMode() ?
                'production' :
                'development',
            'release' => $this->getRelease(),
            'before_send' => $this->beforeSendToSentry()
        ];
    }

    protected function getRelease(): string
    {
        try {
            $composerPath = realpath(Registry::getConfig()->getConfigParam('sShopDir') . '../composer.lock');

            if (!file_exists($composerPath)) {
                throw new FileException(sprintf('composer.lock file not found in path %s', $composerPath));
            }

            return (new DateTimeImmutable())->setTimestamp(filemtime($composerPath))->format('Y-m-d_H:i:s');
        } catch (StandardException) {
            return '';
        }
    }

    protected function getSentryTracesSampleRate(): callable
    {
        return function (SamplingContext $context): float {
            if ($context->getParentSampled()) {
                return 1.0;
            }
            return 0.25;
        };
    }

    protected function beforeSendToSentry(): callable
    {
        return function (SentryEvent $event): ?SentryEvent {
            return $event;
        };
    }
}
