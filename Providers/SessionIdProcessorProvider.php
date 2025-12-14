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
use D3\OxLogIQ\Processors\SessionIdProcessor;
use InvalidArgumentException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

class SessionIdProcessorProvider implements ProviderInterface
{
    /**
     * @param MonologConfiguration         $configuration
     */
    public function __construct(protected MonologConfigurationInterface $configuration)
    {
    }

    public function register(LoggerFactory $factory): void
    {
        try {
            $factory->addOtherProcessor(
                new SessionIdProcessor(Registry::getSession())
            );
        } catch (InvalidArgumentException $exception) {
            error_log('OxLogIQ: '.$exception->getMessage());
        }
    }
}
