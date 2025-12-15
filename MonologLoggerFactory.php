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

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\Interfaces\MonologLoggerFactoryInterface as OxLogIQLoggerFactoryInterface;
use D3\OxLogIQ\Interfaces\ProviderInterface;
use Exception;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Validator\LoggerConfigurationValidatorInterface;
use Psr\Log\LoggerInterface;

class MonologLoggerFactory implements LoggerFactoryInterface, OxLogIQLoggerFactoryInterface
{
    /**
     * @param MonologConfiguration  $configuration
     * @param ProviderInterface[]   $providers
     */
    public function __construct(
        protected MonologConfigurationInterface $configuration,
        LoggerConfigurationValidatorInterface $configurationValidator,
        protected LoggerFactory $loggerFactory,
        protected iterable $providers
    ) {
        $configurationValidator->validate($configuration);
    }

    public function getFactory(): LoggerFactory
    {
        $factory = $this->loggerFactory;

        foreach ($this->providers as $provider) {
            try {
                $provider->register($factory);
            } catch (Exception $exception) {
                error_log('OxLogIQ: '.$exception->getMessage());
            }
        }

        return $factory;
    }

    /**
     * @throws Exception
     */
    public function create(): LoggerInterface
    {
        return $this->getFactory()->build($this->configuration->getLoggerName());
    }
}
