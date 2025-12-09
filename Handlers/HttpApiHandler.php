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

use D3\OxLogIQ\Release\ReleaseServiceInterface;
use DateTime;
use Monolog\Handler\AbstractProcessingHandler;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\ShopVersion;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

class HttpApiHandler extends AbstractProcessingHandler
{
    public function __construct(
        protected $endpoint,
        protected $apiKey,
        protected $level,
        protected ClientInterface $client,
        protected RequestFactoryInterface $requestFactory,
        protected StreamFactoryInterface $streamFactory,
        protected $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        try {
            try {
                $response = $this->client->sendRequest($this->getRequest($record));
            } catch (ClientExceptionInterface $e) {
                throw new RuntimeException("HTTP transport error: ".$e->getMessage());
            }

            if ($response->getStatusCode() >= 400) {
                throw new RuntimeException("API error: ".$response->getStatusCode());
            }
        } catch (RuntimeException $exception) {
            error_log('OxLogIQ: '.$exception->getMessage());
        }
    }

    protected function getRequest(array $record): RequestInterface
    {
        $json = json_encode($this->getData($record));
        $body = $this->streamFactory->createStream($json);

        return $this->requestFactory
            ->createRequest('POST', $this->endpoint)
            ->withHeader('Authorization', $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json')
            ->withBody($body);
    }

    protected function getData(array $record): array
    {
        return [
            'message'       => $record['message'] ?? null,
            'context'       => $record['context'] ?? null,
            'log.level'     => $record['level_name'] ?? null,
            'log.logger'    => $record['channel'] ?? null,
            '@timestamp'    => (isset($record['datetime']) && $record['datetime'] instanceof DateTime) ?
                $record['datetime']->format('c') :
                date('c'),
            'event.dataset' => 'OXID eShop ' . ShopVersion::getVersion(),
            'host.name'     => Registry::getConfig()->getShopUrl(),
            'release'       => $this->getRelease(),
            'call'          => [
                'sid'      => $record['extra']['sid'] ?? null,
                'uid'      => $record['extra']['uid'] ?? null,
                'class'    => $record['extra']['class'] ?? '',
                'function' => $record['extra']['function'] ?? '',
                'line'     => $record['extra']['line'] ?? '',
            ],
        ];
    }

    protected function getRelease(): string
    {
        try {
            $service = $this->getReleaseService();
            return $service->getRelease();
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface) {
            return '';
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getReleaseService(): ReleaseServiceInterface
    {
        return ContainerFactory::getInstance()->getContainer()->get(ReleaseServiceInterface::class);
    }
}
