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
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use OxidEsales\Facts\Config\ConfigFile;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[CoversMethod(Context::class, 'getFactsConfigFile')]
#[CoversMethod(Context::class, 'getRetentionDays')]
#[CoversMethod(Context::class, 'getNotificationMailRecipients' )]
class ContextTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetFactsConfigFile(): void
    {
        $sut = oxNew(Context::class);

        $instance = $this->callMethod($sut, 'getFactsConfigFile');

        $this->assertInstanceOf(
            ConfigFile::class,
            $instance
        );
        $this->assertSame(
            $instance,
            $this->callMethod($sut, 'getFactsConfigFile')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getRemainingLogFilesDataProvider')]
    public function testGetRemainingLogFiles($configuration, $expected): void
    {
        $factsMock = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factsMock->expects($this->atLeastOnce())->method('getVar')
            ->with($this->identicalTo(Context::CONFIGVAR_RETENTIONDAYS))
            ->willReturn($configuration);

        $sut = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getFactsConfigFile'])
            ->getMock();
        $sut->method('getFactsConfigFile')->willReturn($factsMock);

        $this->assertSame(
            $expected,
            $this->callMethod($sut, 'getRetentionDays')
        );
    }

    public static function getRemainingLogFilesDataProvider(): Generator
    {
        yield 'null' => [null, null];
        yield 'integer' => [10, 10];
        yield 'zero' => [0, 0];
        yield 'wrong type' => ['a', null];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getNotificationMailRecipientsDataProvider')]
    public function testGetNotificationMailRecipients($givenValue, $expected): void
    {
        $factsMock = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factsMock->expects($this->once())->method('getVar')
            ->with($this->identicalTo(Context::CONFIGVAR_MAILRECIPIENTS))
            ->willReturn($givenValue);

        $sut = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getFactsConfigFile'])
            ->getMock();
        $sut->method('getFactsConfigFile')->willReturn($factsMock);

        $this->assertSame(
            $expected,
            $this->callMethod($sut, 'getNotificationMailRecipients')
        );
    }

    public static function getNotificationMailRecipientsDataProvider(): Generator
    {
        yield 'null' => [null, null];
        yield 'string' => ['recipientFixture', ['recipientFixture']];
        yield 'array' => [['recipientFixture1', 'recipientFixture2'], ['recipientFixture1', 'recipientFixture2']];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetNotificationMailSubject(): void
    {
        $fixture = 'subjectFixture';

        $factsMock = $this->getMockBuilder(ConfigFile::class)
            ->disableOriginalConstructor()
            ->getMock();
        $factsMock->expects($this->once())->method('getVar')
            ->with($this->identicalTo(Context::CONFIGVAR_MAILSUBJECT))
            ->willReturn($fixture);

        $sut = $this->getMockBuilder(Context::class)
            ->onlyMethods(['getFactsConfigFile'])
            ->getMock();
        $sut->method('getFactsConfigFile')->willReturn($factsMock);

        $this->assertSame(
            $fixture,
            $this->callMethod($sut, 'getNotificationMailSubject')
        );
    }
}