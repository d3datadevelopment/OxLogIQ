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
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use OxidEsales\Facts\Config\ConfigFile;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(Context::class, 'getFactsConfigFile')]
#[CoversMethod(Context::class, 'getRetentionDays')]
#[CoversMethod(Context::class, 'getAlertMailRecipients')]
#[CoversMethod(Context::class, 'getAlertMailLevel')]
#[CoversMethod(Context::class, 'getAlertMailSubject')]
#[CoversMethod(Context::class, 'getAlertMailFrom')]
#[CoversMethod(Context::class, 'getSentryDsn')]
#[CoversMethod(Context::class, 'getHttpApiEndpoint')]
#[CoversMethod(Context::class, 'getHttpApiKey')]
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
    #[DataProvider('getRetentionDaysDataProvider')]
    public function testGetRetentionDays($env, $facts, $expected, $invocationCount): void
    {
        try {
            $_ENV[Context::CONFIGVAR_RETENTIONDAYS] = $env;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($invocationCount))->method('getVar')
                ->with($this->identicalTo(Context::CONFIGVAR_RETENTIONDAYS))
                ->willReturn($facts);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods(['getFactsConfigFile'])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame(
                $expected,
                $this->callMethod($sut, 'getRetentionDays')
            );
        } finally {
            unset($_ENV[Context::CONFIGVAR_RETENTIONDAYS]);
        }
    }

    public static function getRetentionDaysDataProvider(): Generator
    {
        yield 'env string' => ['envValue', 'factsValue', null, 0];
        yield 'env int' => [7, 5, 7, 0];
        yield 'facts string' => [null, 'factsValue', null, 1];
        yield 'facts int' => [null, 5, 5, 1];
        yield 'facts null' => [null, null, null, 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getAlertMailRecipientsDataProvider')]
    public function testGetAlertMailRecipients(
        ?string $givenEnvValue,
        $givenFactsValue,
        $expected,
        int $getVarCount,
        array $exepectedArguments
    ): void {
        try {
            $calls = [];

            $_ENV[Context::CONFIGVAR_MAILRECIPIENTS] = $givenEnvValue;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($getVarCount))->method('getVar')
                ->with(self::callback(function ($arg) use (&$calls) {
                    $calls[] = $arg;
                    return true;
                }))
                ->willReturn($givenFactsValue);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods([ 'getFactsConfigFile' ])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame($expected, $this->callMethod($sut, 'getAlertMailRecipients'));
            $this->assertSame($calls, $exepectedArguments);

        } finally {
            unset($_ENV[Context::CONFIGVAR_MAILRECIPIENTS]);
        }
    }

    public static function getAlertMailRecipientsDataProvider(): Generator
    {
        yield 'env string' => ['recipientEnvFixture', 'recipientFactsFixture', ['recipientEnvFixture'], 0, []];
        yield 'facts string' => [null, 'recipientFixture', ['recipientFixture'], 1, ['oxlogiq_mailRecipients']];
        yield 'facts array' => [null, ['recipientFixture1', 'recipientFixture2'], ['recipientFixture1', 'recipientFixture2'], 1, ['oxlogiq_mailRecipients']];
        yield 'fallback to sAdminEmail' => [null, null, null, 2, ['oxlogiq_mailRecipients', 'sAdminEmail']];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getAlertMailLevelDataProvider')]
    public function testGetAlertMailLevel($env, $facts, $expected, $invocationCount): void
    {
        try {
            $_ENV[Context::CONFIGVAR_MAILLEVEL] = $env;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($invocationCount))->method('getVar')
                ->with($this->identicalTo(Context::CONFIGVAR_MAILLEVEL))
                ->willReturn($facts);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods(['getFactsConfigFile'])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame(
                $expected,
                $this->callMethod($sut, 'getAlertMailLevel')
            );
        } finally {
            unset($_ENV[Context::CONFIGVAR_MAILLEVEL]);
        }
    }

    public static function getAlertMailLevelDataProvider(): Generator
    {
        foreach (self::envDecisionDataProvider() as $key => $value) {
            yield $key => $value;
        }
        yield 'default value' => [null, null, 'ERROR', 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getAlertMailSubjectDataProvider')]
    public function testGetAlertMailSubject($env, $facts, $expected, $invocationCount): void
    {
        try {
            $_ENV[Context::CONFIGVAR_MAILSUBJECT] = $env;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($invocationCount))->method('getVar')
                ->with($this->identicalTo(Context::CONFIGVAR_MAILSUBJECT))
                ->willReturn($facts);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods(['getFactsConfigFile'])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame(
                $expected,
                $this->callMethod($sut, 'getAlertMailSubject')
            );
        } finally {
            unset($_ENV[Context::CONFIGVAR_MAILSUBJECT]);
        }
    }

    public static function getAlertMailSubjectDataProvider(): Generator
    {
        foreach (self::envDecisionDataProvider() as $key => $value) {
            yield $key => $value;
        }
        yield 'default value' => [null, null, 'Shop Log Alert', 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('envDecisionDataProvider')]
    public function testGetAlertMailFromAddress($env, $facts, $expected, $invocationCount): void
    {
        try {
            $_ENV[Context::CONFIGVAR_MAILFROM] = $env;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($invocationCount))->method('getVar')
                ->with($this->identicalTo(Context::CONFIGVAR_MAILFROM))
                ->willReturn($facts);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods(['getFactsConfigFile'])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame(
                $expected,
                $this->callMethod($sut, 'getAlertMailFrom')
            );
        } finally {
            unset($_ENV[Context::CONFIGVAR_MAILFROM]);
        }
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('envDecisionDataProvider')]
    public function testGetSentryDsn($env, $facts, $expected, $invocationCount): void
    {
        try {
            $_ENV[Context::CONFIGVAR_SENTRY_DSN] = $env;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($invocationCount))->method('getVar')
                ->with($this->identicalTo(Context::CONFIGVAR_SENTRY_DSN))
                ->willReturn($facts);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods(['getFactsConfigFile'])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame(
                $expected,
                $this->callMethod($sut, 'getSentryDsn')
            );
        } finally {
            unset($_ENV[Context::CONFIGVAR_SENTRY_DSN]);
        }
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('envDecisionDataProvider')]
    public function testGetHttpApiEndpoint($env, $facts, $expected, $invocationCount): void
    {
        try {
            $_ENV[Context::CONFIGVAR_HTTPAPI_ENDPOINT] = $env;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($invocationCount))->method('getVar')
                ->with($this->identicalTo(Context::CONFIGVAR_HTTPAPI_ENDPOINT))
                ->willReturn($facts);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods(['getFactsConfigFile'])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame(
                $expected,
                $this->callMethod($sut, 'getHttpApiEndpoint')
            );
        } finally {
            unset($_ENV[Context::CONFIGVAR_HTTPAPI_ENDPOINT]);
        }
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('envDecisionDataProvider')]
    public function testGetHttpApiKey($env, $facts, $expected, $invocationCount): void
    {
        try {
            $_ENV[Context::CONFIGVAR_HTTPAPI_KEY] = $env;

            $factsMock = $this->getMockBuilder(ConfigFile::class)
                ->disableOriginalConstructor()
                ->getMock();
            $factsMock->expects($this->exactly($invocationCount))->method('getVar')
                ->with($this->identicalTo(Context::CONFIGVAR_HTTPAPI_KEY))
                ->willReturn($facts);

            $sut = $this->getMockBuilder(Context::class)
                ->onlyMethods(['getFactsConfigFile'])
                ->getMock();
            $sut->method('getFactsConfigFile')->willReturn($factsMock);

            $this->assertSame(
                $expected,
                $this->callMethod($sut, 'getHttpApiKey')
            );
        } finally {
            unset($_ENV[Context::CONFIGVAR_HTTPAPI_KEY]);
        }
    }

    public static function envDecisionDataProvider(): Generator
    {
        yield 'env string' => ['envValue', 'factsValue', 'envValue', 0];
        yield 'facts string' => [null, 'factsValue', 'factsValue', 1];
    }
}
