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

namespace Providers;

use D3\LoggerFactory\LoggerFactory;
use D3\LoggerFactory\Options\MailLoggerHandlerOption;
use D3\OxLogIQ\MonologConfiguration;
use D3\OxLogIQ\Providers\MailHandlerProvider;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(MailHandlerProvider::class, 'register')]
class MailHandlerProviderTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
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
        $configurationMock->expects(self::exactly($invocation))->method('getAlertMailRecipients');
        $configurationMock->method('getAlertMailSubject')->willReturn('subjectFixture');
        $configurationMock->method('getAlertMailFrom')->willReturn('fromFixture');
        $configurationMock->method('getAlertMailLevel')->willReturn('error');

        $sut = oxNew(MailHandlerProvider::class, $configurationMock);

        $mailLoggerHandlerOptionMock = $this->getMockBuilder(MailLoggerHandlerOption::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setBuffering'])
            ->getMock();
        $mailLoggerHandlerOptionMock->expects(self::exactly($invocation))->method('setBuffering');

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addMailHandler'])
            ->getMock();
        $factoryMock->expects(self::exactly($invocation))->method('addMailHandler')
            ->willReturn($mailLoggerHandlerOptionMock);

        $this->callMethod($sut, 'register', [$factoryMock]);
    }

    public static function registerDataProvider(): Generator
    {
        yield 'no mail address' => [false, 0];
        yield 'given mail addresses' => [true, 1];
    }
}