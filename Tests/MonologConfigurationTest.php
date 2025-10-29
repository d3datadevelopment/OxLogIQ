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

use D3\OxLogiQ\Context;
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

#[CoversMethod(MonologConfiguration::class, '__construct')]
#[CoversMethod(MonologConfiguration::class, 'getLoggerName')]
#[CoversMethod(MonologConfiguration::class, 'getLogFilePath')]
#[CoversMethod(MonologConfiguration::class, 'getLogLevel')]
#[CoversMethod(MonologConfiguration::class, 'getRetentionDays' )]
#[CoversMethod(MonologConfiguration::class, 'hasNotificationMailRecipient' )]
#[CoversMethod(MonologConfiguration::class, 'getNotificationMailRecipients' )]
#[CoversMethod(MonologConfiguration::class, 'getNotificationMailLevel')]
#[CoversMethod(MonologConfiguration::class, 'getNotificationMailSubject')]
#[CoversMethod(MonologConfiguration::class, 'getNotificationMailFrom')]
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

        $contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(get_class_methods(Context::class))
            ->getMock();
        $contextMock->method('getRetentionDays')->willReturn(5);
        $contextMock->method('getNotificationMailRecipients')->willReturn(['test@example.dev']);
        $contextMock->method('getNotificationMailLevel')->willReturn('warning');
        $contextMock->method('getNotificationMailSubject')->willReturn('mySubject');
        $contextMock->method('getNotificationMailFrom')->willReturn('fromAddress');

        $this->sut = $this->getMockBuilder(MonologConfiguration::class)
            ->setConstructorArgs([
                $configurationMock,
                $configMock,
                $contextMock
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

    public static function getLogLevelDataProvider(): Generator
    {
        yield 'lowercase known'   => ['alert', false, 'ALERT'];
        yield 'uppercase known'   => ['CRITICAL', false, 'CRITICAL'];
        yield 'unknown'   => ['unknown', true, 'CRITICAL'];
    }

    #[Test]
    public function testGetRetentionDays(): void
    {
        self::assertSame(
            5,
            $this->sut->getRetentionDays()
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getNotificationMailRecipientsDataProvider')]
    public function testGetNotificationMailRecipients($recipients, bool $isset, $expected)
    {
        $configurationMock = new OxidMonologConfiguration(
            'myLogger',
            '/var/log/oxidlog.log',
            'WARNING'
        );

        $configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(get_class_methods(Context::class))
            ->getMock();
        $contextMock->method('getNotificationMailRecipients')->willReturn($recipients);

        $sut = new MonologConfiguration($configurationMock, $configMock, $contextMock);

        self::assertSame(
            $isset,
            $this->callMethod($sut, 'hasNotificationMailRecipient')
        );
        self::assertSame(
            $expected,
            $this->callMethod($sut, 'getNotificationMailRecipients')
        );
    }

    public static function getNotificationMailRecipientsDataProvider(): Generator
    {
        yield 'not set' => [null, false, null];
        yield 'set' => [['test@example.dev'], true, ['test@example.dev']];
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

        $contextMock = $this->getMockBuilder(Context::class)
            ->onlyMethods(get_class_methods(Context::class))
            ->getMock();
        $contextMock->method('getNotificationMailLevel')->willReturn($givenLevel);

        $sut = new MonologConfiguration(
            $configurationMock,
            $configMock,
            $contextMock
        );

        if ($exceptionExpected) {
            $this->expectException( InvalidArgumentException::class);
        }

        self::assertSame(
            $expected,
            $this->callMethod($sut, 'getNotificationMailLevel')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetNotificationMailSubject()
    {
        self::assertSame(
            'mySubject',
            $this->callMethod($this->sut, 'getNotificationMailSubject')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetNotificationMailFrom()
    {
        self::assertSame(
            'fromAddress',
            $this->callMethod($this->sut, 'getNotificationMailFrom')
        );
    }
}