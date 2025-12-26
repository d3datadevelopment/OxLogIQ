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

namespace D3\OxLogIQ\Tests\Providers\Handlers;

use D3\LoggerFactory\LoggerFactory;
use D3\LoggerFactory\Options\MailLoggerHandlerOption;
use D3\OxLogIQ\MonologConfiguration;
use D3\OxLogIQ\Providers\Handlers\MailHandlerProvider;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use OxidEsales\Eshop\Application\Model\Shop;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(MailHandlerProvider::class, 'isActive')]
#[CoversMethod(MailHandlerProvider::class, 'provide')]
class MailHandlerProviderTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
     * @dataProvider isActiveDataProvider
     */
    #[Test]
    #[DataProvider('isActiveDataProvider')]
    public function testIsActive($useMailAlert): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['useAlertMail'])
            ->getMock();
        $configurationMock->method('useAlertMail')->willReturn($useMailAlert);

        $sut = oxNew(MailHandlerProvider::class, $configurationMock, Registry::getConfig());

        $this->assertSame(
            $useMailAlert,
            $this->callMethod(
                $sut,
                'isActive',
            )
        );
    }

    public static function isActiveDataProvider(): Generator
    {
        yield [false];
        yield [true];
    }

    /**
     * @throws ReflectionException
     * @dataProvider registerDataProvider
     */
    #[Test]
    #[DataProvider('registerDataProvider')]
    public function testRegister(bool $useMailAlert, int $invocation): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['useAlertMail', 'getAlertMailRecipients', 'getAlertMailSubject', 'getAlertMailFrom', 'getAlertMailLevel'])
            ->getMock();
        $configurationMock->method('useAlertMail')->willReturn($useMailAlert);
        $configurationMock->expects(self::once())->method('getAlertMailRecipients');
        $configurationMock->method('getAlertMailSubject')->willReturn('subjectFixture');
        $configurationMock->method('getAlertMailFrom')->willReturn('fromFixture');
        $configurationMock->method('getAlertMailLevel')->willReturn('error');

        $shopMock = $this->getMockBuilder(Shop::class)
            ->disableOriginalConstructor()
            ->getMock();

        $shopConfigMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(get_class_methods(Config::class))
            ->disableOriginalConstructor()
            ->getMock();
        $shopConfigMock->method('getActiveShop')->willReturn($shopMock);

        $sut = oxNew(MailHandlerProvider::class, $configurationMock, $shopConfigMock);

        $mailLoggerHandlerOptionMock = $this->getMockBuilder(MailLoggerHandlerOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setBuffering'])
            ->getMock();
        $mailLoggerHandlerOptionMock->expects(self::once())->method('setBuffering');

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addMailHandler'])
            ->getMock();
        $factoryMock->expects(self::once())->method('addMailHandler')
            ->willReturn($mailLoggerHandlerOptionMock);

        $this->callMethod($sut, 'provide', [$factoryMock]);
    }

    public static function registerDataProvider(): Generator
    {
        yield 'no mail address' => [false, 0];
        yield 'given mail addresses' => [true, 1];
    }
}
