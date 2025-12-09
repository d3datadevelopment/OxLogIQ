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

namespace D3\OxLogIQ\Tests\Handlers\Http;

use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class RequestFactoryStub implements RequestFactoryInterface, StreamFactoryInterface
{
    #[NoReturn]
    public function createRequest( string $method, $uri ): RequestInterface
    {
        exit();
    }

    #[NoReturn]
    public function createStream( string $content = '' ): StreamInterface
    {
        exit();
    }

    #[NoReturn]
    public function createStreamFromFile( string $filename, string $mode = 'r' ): StreamInterface
    {
        exit();
    }

    #[NoReturn]
    public function createStreamFromResource( $resource ): StreamInterface
    {
        exit();
    }
}