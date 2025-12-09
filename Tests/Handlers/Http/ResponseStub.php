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
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseStub implements ResponseInterface
{
    #[NoReturn]
    public function getProtocolVersion(): string
    {
        exit();
    }

    #[NoReturn]
    public function withProtocolVersion( string $version ): MessageInterface
    {
        exit();
    }

    #[NoReturn]
    public function getHeaders(): array
    {
        exit();
    }

    #[NoReturn]
    public function hasHeader( string $name ): bool
    {
        exit();
    }

    #[NoReturn]
    public function getHeader( string $name ): array
    {
        exit();
    }

    #[NoReturn]
    public function getHeaderLine( string $name ): string
    {
        exit();
    }

    #[NoReturn]
    public function withHeader( string $name, $value ): MessageInterface
    {
        exit();
    }

    #[NoReturn]
    public function withAddedHeader( string $name, $value ): MessageInterface
    {
        exit();
    }

    #[NoReturn]
    public function withoutHeader( string $name ): MessageInterface
    {
        exit();
    }

    #[NoReturn]
    public function getBody(): StreamInterface
    {
        exit();
    }

    #[NoReturn]
    public function withBody( StreamInterface $body ): MessageInterface
    {
        exit();
    }

    #[NoReturn]
    public function getStatusCode(): int
    {
        exit();
    }

    #[NoReturn]
    public function withStatus( int $code, string $reasonPhrase = '' ): ResponseInterface
    {
        exit();
    }

    #[NoReturn]
    public function getReasonPhrase(): string
    {
        exit();
    }
}