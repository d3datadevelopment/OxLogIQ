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

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\Interfaces\ProviderInterface;
use D3\OxLogIQ\MonologConfiguration;
use Elastica\Client;
use Exception;
use Monolog\Handler\ElasticSearchHandler;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;

class ElasticSearchHandlerProvider implements ProviderInterface
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
            $factory->addOtherHandler(
                new ElasticSearchHandler(
                    $this->getElasticSearchClient(),
                    ['index' => 'logs'] // adjust to your needs
                )
            )->setBuffering();
        } catch (Exception $exception) {
            error_log('OxLogIQ: '.$exception->getMessage());
        }
    }

    protected function getElasticSearchClient(): Client
    {
        // add your configuration
        return new Client([
            'host'      => 'https://<your-project>.es.<region>.aws.elastic-cloud.com',
            'port'      => 443,
            'transport' => 'Https',
            'headers'   => [
                'Authorization' => 'ApiKey <your-API-key>',
                'Content-Type'  => 'application/json',
            ],
        ]);
    }
}
