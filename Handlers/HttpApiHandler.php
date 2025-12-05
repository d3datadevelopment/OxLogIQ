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

namespace D3\OxLogIQ\Handlers;

use D3\OxLogIQ\MonologConfiguration;
use D3\OxLogIQ\Release\ReleaseServiceInterface;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfigurationInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HttpApiHandler extends AbstractProcessingHandler
{
    protected Client $httpClient;

    public function __construct(
        protected $endpoint,
        $apiKey,
        $level = Logger::DEBUG,
        $bubble = true,
        Client $client = null
    ) {
        parent::__construct($level, $bubble);

        $this->httpClient = $client ?? new Client([
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    protected function write(array $record): void
    {
        try {
            $this->httpClient->post(
                $this->endpoint,
                [
                    'json' => [
                        'message'       => $record['message'],
                        'context'       => $record['context'],
                        'log.level'     => $record['level_name'],
                        'log.logger'    => $record['channel'],
                        '@timestamp'    => $record['datetime'] instanceof DateTime ?
                            $record['datetime']->format('c') :
                            date('c'),
                        'event.dataset' => 'OXID eShop ' . ShopVersion::getVersion(),
                        'host.name'     => Registry::getConfig()->getShopUrl(),
                        'release'       => $this->getRelease(),
                        'call'          => [
                            'sid'      => $record['extra']['sid'],
                            'uid'      => $record['extra']['uid'],
                            'class'    => $record['extra']['class'] ?? '',
                            'function' => $record['extra']['function'] ?? '',
                            'line'     => $record['extra']['line'] ?? '',
                        ],
                    ],
                ]
            );
        } catch (GuzzleException) {
        }
    }

    protected function getRelease(): string
    {
        /** @var ReleaseServiceInterface $service */
        try {
            $service = ContainerFactory::getInstance()->getContainer()->get(ReleaseServiceInterface::class);
            return $service->getRelease();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface) {
            return '';
        }
    }
}
