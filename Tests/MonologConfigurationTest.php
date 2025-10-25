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

namespace D3\OxLogiQ\Tests;

use D3\OxLogiQ\MonologConfiguration;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfiguration as OxidMonologConfiguration;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[CoversMethod(MonologConfiguration::class, 'getLoggerName')]
#[CoversMethod(MonologConfiguration::class, 'getLogFilePath')]
#[CoversMethod(MonologConfiguration::class, 'getLogLevel')]
#[CoversMethod(MonologConfiguration::class, 'getRemainingFiles')]
#[CoversMethod(MonologConfiguration::class, 'hasNotificationMailAddress')]
#[CoversMethod(MonologConfiguration::class, 'getNotificationMailAddress')]
class MonologConfigurationTest extends TestCase
{
    use CanAccessRestricted;

    protected MonologConfiguration $sut;

    public function setUp(): void
    {
        parent::setUp();

        $configurationMock = new OxidMonologConfiguration(
            'myLogger',
            '/var/log/oxidlog.log',
            'WARNING'
        );

        $shopMock = $this->getMockBuilder(Shop::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $shopMock->method('getId')->willReturn(3);

        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getActiveShop'])
            ->getMock();
        $configMock->method('getActiveShop')->willReturn($shopMock);

        $this->sut = $this->getMockBuilder(MonologConfiguration::class)
            ->setConstructorArgs([
                $configurationMock,
                $configMock,
                5,
                'test@example.dev'
            ])
            ->onlyMethods(['getContext'])
            ->getMock();
        $this->sut->method('getContext')->willReturn('contextFixture');
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetLoggerName(): void
    {
        $loggerName = $this->callMethod($this->sut, 'getLoggerName');

        self::assertStringContainsString('myLogger', $loggerName);
        self::assertStringContainsString('shp-3', $loggerName);
        self::assertStringContainsString('contextFixture', $loggerName);
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetLogFilePath(): void
    {
        self::assertSame(
            '/var/log/oxidlog.log',
            $this->callMethod($this->sut, 'getLogFilePath')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetLogLevel(): void
    {
        self::assertSame(
            'WARNING',
            $this->callMethod($this->sut, 'getLogLevel')
        );
    }

    #[Test]
    public function testGetRemainingFiles(): void
    {
        self::assertSame(
            5,
            $this->sut->getRemainingFiles()
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getNotificationMailAddressDataProvider')]
    public function testGetNotificationMailAddress($mailAddress, bool $isset, $expected)
    {
        $this->setValue($this->sut, 'notificationMailAddress', $mailAddress);

        self::assertSame(
            $isset,
            $this->callMethod($this->sut, 'hasNotificationMailAddress')
        );
        self::assertSame(
            $expected,
            $this->callMethod($this->sut, 'getNotificationMailAddress')
        );
    }

    public static function getNotificationMailAddressDataProvider(): Generator
    {
        yield 'not set' => [null, false, null];
        yield 'set' => ['test@example.dev', true, 'test@example.dev'];
    }
}