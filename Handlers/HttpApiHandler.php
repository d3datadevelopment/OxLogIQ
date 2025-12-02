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

use GuzzleHttp\Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class HttpApiHandler extends AbstractProcessingHandler
{
    protected Client $httpClient;

    public function __construct(protected $endpoint, $apiKey, $level = Logger::DEBUG, $bubble = true, Client $client = null)
    {
        parent::__construct($level, $bubble);

        $this->httpClient = $client ?? new Client([
            'headers' => [
                'Authorization' => $apiKey,
                'Content-Type'  => 'application/json',
            ]
        ]);
    }

    protected function write(array $record): void
    {
        $this->httpClient->post(
            $this->endpoint,
            [
                'json' => [
                    'message' => $record['message'],
                    'context' => $record['context'],
                    'level' => $record['level_name'],
                    '@timestamp' => $record['datetime']->format(DATE_ATOM),
                    'channel' => $record['channel'],
                    'release' => '',        // retrieve it from configuration
                    'sid'   => $record['extra']['sid'],
                    'uid'   => $record['extra']['uid'],
                    'class'   => $record['extra']['class'] ?? '',
                    'function'   => $record['extra']['function'] ?? '',
                    'line'   => $record['extra']['line'] ?? '',
                ]
            ]
        );
    }
}