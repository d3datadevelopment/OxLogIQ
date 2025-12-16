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

namespace D3\OxLogIQ\Tests\Core;

use D3\OxLogIQ\Core\FallbackLogger;
use D3\OxLogIQ\Handlers\FallbackHandler;
use D3\TestingTools\Development\CanAccessRestricted;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;

#[Small]
#[CoversMethod(FallbackLogger::class, 'get')]
class FallbackLoggerTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGet(): void
    {
        $handlerMock = $this->getMockBuilder(FallbackHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new FallbackLogger($handlerMock);

        /** @var Logger $logger */
        $logger = $this->callMethod(
            $sut,
            'get'
        );

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertStringContainsString('Fallback', $logger->getName());
        $this->assertSame($handlerMock, $logger->getHandlers()[0]);
    }
}