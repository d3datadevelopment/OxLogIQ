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

namespace D3\OxLogIQ\Providers;

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\Interfaces\ProviderInterface;
use D3\OxLogIQ\MonologConfiguration;
use D3\OxLogIQ\Processors\SentryExceptionProcessor;
use Monolog\Logger;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sentry\Monolog\BreadcrumbHandler;
use Sentry\Monolog\Handler;
use Sentry\SentrySdk;

use function Sentry\init;

class SentryHandlerProvider implements ProviderInterface
{
    /**
     * @param MonologConfiguration         $configuration
     */
    public function __construct(protected MonologConfigurationInterface $configuration)
    {
    }

    public function register(LoggerFactory $factory): void
    {
        if ($this->configuration->hasSentryDsn()) {
            try {
                init($this->configuration->getSentryOptions());

                $factory->addOtherHandler(
                    (new BreadcrumbHandler(
                        SentrySdk::getCurrentHub(),
                        Logger::INFO
                    ))
                )->setLogOnErrorOnly(
                    $this->configuration->getLogLevel()
                );

                $factory->addOtherHandler(
                    (new Handler(
                        SentrySdk::getCurrentHub(),
                        Logger::toMonologLevel($this->configuration->getLogLevel())
                    ))
                        ->pushProcessor(new SentryExceptionProcessor())
                )->setBuffering();
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $exception) {
                error_log('OxLogIQ: '.$exception->getMessage());
            }
        }
    }
}
