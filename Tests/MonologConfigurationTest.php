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
use InvalidArgumentException;
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
#[CoversMethod(MonologConfiguration::class, 'hasNotificationMailRecipient' )]
#[CoversMethod(MonologConfiguration::class, 'getNotificationMailRecipients' )]
#[CoversMethod(MonologConfiguration::class, 'getNotificationMailLevel')]
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
                'test@example.dev',
                'error'
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
    #[DataProvider('getLogLevelDataProvider')]
    public function testGetLogLevel($givenLevel, $exceptionExpected, $expected): void
    {
        $configurationMock = new OxidMonologConfiguration(
            'myLogger',
            '/var/log/oxidlog.log',
            $givenLevel
        );

        $configMock = $this->getMockBuilder(Config::class)
           ->disableOriginalConstructor()
           ->getMock();

        $sut = new MonologConfiguration(
            $configurationMock,
            $configMock,
            5,
            'test@example.dev',
            'error'
        );

        if ($exceptionExpected) {
            $this->expectException(InvalidArgumentException::class);
        }

        self::assertSame(
            $expected,
            $this->callMethod($sut, 'getLogLevel')
        );
    }

    public static function getLogLevelDataProvider(): Generator
    {
        yield 'lowercase known'   => ['alert', false, 'ALERT'];
        yield 'uppercase known'   => ['CRITICAL', false, 'CRITICAL'];
        yield 'unknown'   => ['unknown', true, 'CRITICAL'];
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
    #[DataProvider('getNotificationMailRecipientsDataProvider')]
    public function testGetNotificationMailRecipients($recipients, bool $isset, $expected)
    {
        $this->setValue($this->sut, 'notificationMailRecipients', $recipients);

        self::assertSame(
            $isset,
            $this->callMethod($this->sut, 'hasNotificationMailRecipient')
        );
        self::assertSame(
            $expected,
            $this->callMethod($this->sut, 'getNotificationMailRecipients')
        );
    }

    public static function getNotificationMailRecipientsDataProvider(): Generator
    {
        yield 'not set' => [null, false, null];
        yield 'set' => ['test@example.dev', true, 'test@example.dev'];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getLogLevelDataProvider')]
    public function testGetNotificationMailLevel($givenLevel, $exceptionExpected, $expected): void
    {
        $configurationMock = new OxidMonologConfiguration(
            'myLogger',
            '/var/log/oxidlog.log',
            'error'
        );

        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new MonologConfiguration(
            $configurationMock,
            $configMock,
            5,
            'test@example.dev',
            $givenLevel
        );

        if ($exceptionExpected) {
            $this->expectException( InvalidArgumentException::class);
        }

        self::assertSame(
            $expected,
            $this->callMethod($sut, 'getNotificationMailLevel')
        );
    }
}