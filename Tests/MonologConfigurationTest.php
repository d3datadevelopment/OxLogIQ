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

namespace D3\OxLogIQ\Tests;

use D3\OxLogIQ\Context;
use D3\OxLogIQ\MonologConfiguration;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use InvalidArgumentException;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Configuration\MonologConfiguration as OxidMonologConfiguration;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(MonologConfiguration::class, '__construct')]
#[CoversMethod(MonologConfiguration::class, 'getLoggerName')]
#[CoversMethod(MonologConfiguration::class, 'getLogFilePath')]
#[CoversMethod(MonologConfiguration::class, 'getLogLevel')]
#[CoversMethod(MonologConfiguration::class, 'getRetentionDays')]
#[CoversMethod(MonologConfiguration::class, 'useAlertMail')]
#[CoversMethod(MonologConfiguration::class, 'hasAlertMailRecipient')]
#[CoversMethod(MonologConfiguration::class, 'getAlertMailRecipients')]
#[CoversMethod(MonologConfiguration::class, 'getAlertMailLevel')]
#[CoversMethod(MonologConfiguration::class, 'getAlertMailSubject')]
#[CoversMethod(MonologConfiguration::class, 'getAlertMailFrom')]
#[CoversMethod(MonologConfiguration::class, 'getRelease')]
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
        $contextMock->method('getAlertMailRecipients')->willReturn(['test@example.dev']);
        $contextMock->method('getAlertMailLevel')->willReturn('warning');
        $contextMock->method('getAlertMailSubject')->willReturn('mySubject');
        $contextMock->method('getAlertMailFrom')->willReturn('fromAddress');

        $this->sut = $this->getMockBuilder(MonologConfiguration::class)
            ->setConstructorArgs([
                $configurationMock,
                $configMock,
                $contextMock,
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
     * @dataProvider useAlertMailDataProvider
     */
    #[Test]
    #[DataProvider('useAlertMailDataProvider')]
    public function testUseAlertMail(bool $useMail, bool $hasRecipient, bool $expected): void
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
        $contextMock->method('useAlertMail')->willReturn($useMail);

        $sut = $this->getMockBuilder(MonologConfiguration::class)
            ->setConstructorArgs([$configurationMock, $configMock, $contextMock])
            ->onlyMethods(['hasAlertMailRecipient'])
            ->getMock();
        $sut->method('hasAlertMailRecipient')->willReturn($hasRecipient);

        $this->assertSame(
            $expected,
            $this->callMethod($sut, 'useAlertMail')
        );
    }

    public static function useAlertMailDataProvider(): Generator
    {
        yield 'toggle off, no recipient'   => [false, false, false];
        yield 'toggle on, no recipient'   => [true, false, false];
        yield 'toggle on, has recipient'   => [true, true, true];
        yield 'toggle off, has recipient'   => [false, true, false];
    }

    /**
     * @throws ReflectionException
     * @dataProvider getAlertMailRecipientsDataProvider
     */
    #[Test]
    #[DataProvider('getAlertMailRecipientsDataProvider')]
    public function testGetAlertMailRecipients($recipients, bool $isset, $expected): void
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
        $contextMock->method('getAlertMailRecipients')->willReturn($recipients);

        $sut = new MonologConfiguration($configurationMock, $configMock, $contextMock);

        self::assertSame(
            $isset,
            $this->callMethod($sut, 'hasAlertMailRecipient')
        );
        self::assertSame(
            $expected,
            $this->callMethod($sut, 'getAlertMailRecipients')
        );
    }

    public static function getAlertMailRecipientsDataProvider(): Generator
    {
        yield 'not set' => [null, false, null];
        yield 'set' => [['test@example.dev'], true, ['test@example.dev']];
    }

    /**
     * @throws ReflectionException
     * @dataProvider getLogLevelDataProvider
     */
    #[Test]
    #[DataProvider('getLogLevelDataProvider')]
    public function testgetAlertMailLevel($givenLevel, $exceptionExpected, $expected): void
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
        $contextMock->method('getAlertMailLevel')->willReturn($givenLevel);

        $sut = new MonologConfiguration(
            $configurationMock,
            $configMock,
            $contextMock
        );

        if ($exceptionExpected) {
            $this->expectException(InvalidArgumentException::class);
        }

        self::assertSame(
            $expected,
            $this->callMethod($sut, 'getAlertMailLevel')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testgetAlertMailSubject(): void
    {
        self::assertSame(
            'mySubject',
            $this->callMethod($this->sut, 'getAlertMailSubject')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testgetAlertMailFrom(): void
    {
        self::assertSame(
            'fromAddress',
            $this->callMethod($this->sut, 'getAlertMailFrom')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetRelease(): void
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
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new MonologConfiguration(
            $configurationMock,
            $configMock,
            $contextMock
        );

        $re = '/^(((\d{4})(-)(0[13578]|10|12)(-)(0[1-9]|[12][0-9]|3[01]))|((\d{4})(-)(0[469]|1‌​1)(-)([0][1-9]|[12][0-9]|30))|((\d{4})(-)(02)(-)(0[1-9]|1[0-9]|2[0-8]))|(([02468]‌​[048]00)(-)(02)(-)(29))|(([13579][26]00)(-)(02)(-)(29))|(([0-9][0-9][0][48])(-)(0‌​2)(-)(29))|(([0-9][0-9][2468][048])(-)(02)(-)(29))|(([0-9][0-9][13579][26])(-)(02‌​)(-)(29)))(_([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9]))$/m';

        $this->assertMatchesRegularExpression(
            $re,
            $this->callMethod($sut, 'getRelease')
        );
    }
}
