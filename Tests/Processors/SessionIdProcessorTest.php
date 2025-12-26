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

namespace D3\OxLogIQ\Tests\Processors;

use D3\OxLogIQ\Processors\SessionIdProcessor;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use InvalidArgumentException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Session;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(SessionIdProcessor::class, '__construct')]
#[CoversMethod(SessionIdProcessor::class, '__invoke')]
#[CoversMethod(SessionIdProcessor::class, 'getShopSid')]
class SessionIdProcessorTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testConstruct(): void
    {
        $sut = $this->getMockBuilder(SessionIdProcessor::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getShopSid'])
            ->getMock();
        $sut->method('getShopSid')->willReturn('012345678901234567890');

        $this->callMethod($sut, '__construct', [Registry::getSession(), 10]);
        self::assertSame(
            '012345678901234567890',
            $this->getValue($sut, 'sid')
        );

        $this->expectException(InvalidArgumentException::class);
        $this->callMethod($sut, '__construct', [Registry::getSession(), 0]);

        $this->expectException(InvalidArgumentException::class);
        $this->callMethod($sut, '__construct', [Registry::getSession(), 33]);
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testInvoke(): void
    {
        $sut = new SessionIdProcessor(Registry::getSession());

        $this->setValue($sut, 'sid', 'sidFixture');

        $records = [
            'foo' => 'bar',
            'extra' => ['context' => 'abc'],
        ];

        self::assertSame(
            ['foo' => 'bar', 'extra' => ['context' => 'abc', 'sid'  => 'sidFixture']],
            $this->callMethod($sut, '__invoke', [$records])
        );
    }

    /**
     * @throws ReflectionException
     * @dataProvider getShopSidDataProvider
     */
    #[Test]
    #[DataProvider('getShopSidDataProvider')]
    public function testGetShopSid(?string $givenSid, int $length, $expected): void
    {
        $sessionMock = $this->getMockBuilder(Session::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $sessionMock->method('getId')->willReturn($givenSid);

        $sut = new SessionIdProcessor($sessionMock);

        self::assertSame(
            $expected,
            $this->callMethod($sut, 'getShopSid', [$length])
        );
    }

    public static function getShopSidDataProvider(): Generator
    {
        yield 'no session' => [null, 32, ''];
        yield 'session 5 chars' => ['12345678901234567890123456789012345', 5, '12345'];
        yield 'session 10 chars' => ['12345678901234567890123456789012345', 10, '1234567890'];
        yield 'session 32 chars' => ['12345678901234567890123456789012345', 32, '12345678901234567890123456789012'];
    }
}
