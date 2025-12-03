<?php

namespace D3\LoggerExtension;

use D3\LoggerFactory\LoggerFactory;
use D3\OxLogIQ\MonologLoggerFactory;
use Elastica\Client;
use Monolog\Handler\ElasticSearchHandler;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Factory\LoggerFactoryInterface;
use Psr\Log\LoggerInterface;

class LoggerFactoryExtension implements LoggerFactoryInterface
{
    /**
     * @param MonologLoggerFactory $innerConfig
     */
    public function __construct(
        protected LoggerFactoryInterface $innerConfig,
        protected MonologConfigurationInterface $configuration
    ) {}

    public function getFactory(): LoggerFactory
    {
        $factory = $this->innerConfig->getFactory();
        $factory->addOtherHandler(
            new ElasticSearchHandler(
                $this->getElasticSearchClient(),
                ['index' => 'logs'] // adjust to your needs
            )
        );

        return $factory;
    }

    public function create(): LoggerInterface
    {
        return $this->getFactory()->build($this->configuration->getLoggerName());
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
                  'Content-Type'  => 'application/json'
              ]
          ]);
    }
}