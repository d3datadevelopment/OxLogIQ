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

use D3\LoggerFactory\LoggerFactory;
use D3\LoggerFactory\Options\FileLoggerHandlerOption;
use D3\OxLogIQ\MonologConfiguration;
use D3\OxLogIQ\MonologLoggerFactory;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OxidEsales\EshopCommunity\Internal\Framework\Logger\Validator\LoggerConfigurationValidatorInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

#[Small]
#[CoversMethod(MonologLoggerFactory::class, '__construct')]
#[CoversMethod(MonologLoggerFactory::class, 'getFactory')]
#[CoversMethod(MonologLoggerFactory::class, 'create')]
#[CoversMethod(MonologLoggerFactory::class, 'addFileHandler')]
#[CoversMethod(MonologLoggerFactory::class, 'getFormatter')]
#[CoversMethod(MonologLoggerFactory::class, 'addMailHandler')]
#[CoversMethod(MonologLoggerFactory::class, 'addSentryHandler')]
#[CoversMethod(MonologLoggerFactory::class, 'addHttpApiHandler')]
#[CoversMethod(MonologLoggerFactory::class, 'addProcessors')]
class MonologLoggerFactoryTest extends TestCase
{
    use CanAccessRestricted;

    protected MonologLoggerFactory $sut;
    protected $logFile = __DIR__ . '/test-error.log';

    public function setUp(): void
    {
        parent::setUp();

        ini_set('error_log', $this->logFile);
        ini_set('log_errors', '1');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        @unlink($this->logFile);
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testConstruct(): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['validate'])
            ->getMock();
        $validatorMock->expects(self::atLeastOnce())->method('validate');

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->callMethod(
            $sut,
            '__construct',
            [
                $configurationMock,
                $validatorMock,
                LoggerFactory::create(),
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetFactory(): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->onlyMethods(
                ['addFileHandler', 'addMailHandler', 'addSentryHandler', 'addHttpApiHandler', 'addProcessors']
            )
            ->setConstructorArgs([$configurationMock, $validatorMock, $factoryMock])
            ->getMock();
        $sut->expects(self::once())->method('addFileHandler');
        $sut->expects(self::once())->method('addMailHandler');
        $sut->expects(self::once())->method('addSentryHandler');
        $sut->expects(self::once())->method('addHttpApiHandler');
        $sut->expects(self::once())->method('addProcessors');

        self::assertInstanceOf(
            LoggerFactory::class,
            $this->callMethod($sut, 'getFactory')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testCreate(): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['build'])
            ->getMock();
        $factoryMock->expects(self::once())->method('build');

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->setConstructorArgs([$configurationMock, $validatorMock, $factoryMock])
            ->onlyMethods(['getFactory'])
            ->getMock();
        $sut->method('getFactory')->willReturn($factoryMock);

        $this->callMethod($sut, 'create');
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('addFileHandlerDataProvider')]
    public function testAddFileHandler(bool $throwException, int $invocationCount): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods([ 'getLogLevel', 'getLogFilePath', 'getRetentionDays' ])
            ->getMock();
        $configurationMock->method('getLogLevel')->willReturn('error');
        $configurationMock->method('getLogFilePath')->willReturn('/var/log/error.log');
        $configurationMock->method('getRetentionDays')->willReturn(5);

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setValue($sut, 'configuration', $configurationMock);

        $fileHandlerMock = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setFormatter'])
            ->getMock();
        $fileHandlerMock->expects(self::exactly($invocationCount))->method('setFormatter');

        $fileLoggerHandlerOptionMock = $this->getMockBuilder(FileLoggerHandlerOption::class)
            ->setConstructorArgs(['/var/log/error.log'])
            ->onlyMethods(['getHandler', 'setBuffering'])
            ->getMock();
        $fileLoggerHandlerOptionMock->method('getHandler')->willReturn($fileHandlerMock);
        $fileLoggerHandlerOptionMock->expects(self::exactly($invocationCount))->method('setBuffering');

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFileHandler'])
            ->getMock();
        if ($throwException) {
            $factoryMock->expects(self::once())->method('addFileHandler')
                ->willThrowException(new \Exception('excMsg'));
        } else {
            $factoryMock->expects(self::once())->method('addFileHandler')
                ->willReturn($fileLoggerHandlerOptionMock);
        }

        $this->callMethod($sut, 'addFileHandler', [$factoryMock]);

        if ($throwException) {
            $this->assertStringContainsString(
                'excMsg',
                file_get_contents($this->logFile)
            );
        }
    }

    public static function addFileHandlerDataProvider(): Generator
    {
        yield 'do not throw exception' => [false, 1];
        yield 'throw exception' => [true, 0];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetFormatter(): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = new MonologLoggerFactory(
            $configurationMock,
            $validatorMock,
            LoggerFactory::create()
        );

        self::assertInstanceOf(
            LineFormatter::class,
            $this->callMethod($sut, 'getFormatter')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('addMailHandlerDataProvider')]
    public function testAddMailHandler(bool $useMailAlert, $address, bool $throwException, int $invocation): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'useAlertMail',
                'getAlertMailRecipients',
                'getAlertMailLevel',
                'getAlertMailSubject',
                'getAlertMailFrom',
            ])
            ->getMock();
        $configurationMock->method('useAlertMail')->willReturn($useMailAlert);
        $configurationMock->expects($this->exactly($invocation))
            ->method('getAlertMailRecipients')->willReturn($address);
        $throwException ?
            $configurationMock->expects($this->exactly($invocation))
                ->method('getAlertMailLevel')->willThrowException(
                    new InvalidArgumentException('excMsg')
                ) :
            $configurationMock->expects($this->exactly($invocation))
                ->method('getAlertMailLevel')->willReturn('error');
        $configurationMock->expects($this->exactly($invocation))
            ->method('getAlertMailSubject')->willReturn('mySubject');
        $configurationMock->expects($this->exactly($invocation))
            ->method('getAlertMailFrom')->willReturn('fromAddress');

        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addMailHandler'])
            ->getMock();
        $factoryMock->expects($this->exactly($throwException ? 0 : $invocation))->method('addMailHandler');

        $sut = new MonologLoggerFactory(
            $configurationMock,
            $validatorMock,
            LoggerFactory::create()
        );

        $this->callMethod($sut, 'addMailHandler', [$factoryMock]);

        if ($throwException) {
            $this->assertStringContainsString(
                'excMsg',
                file_get_contents($this->logFile)
            );
        }
    }

    public static function addMailHandlerDataProvider(): Generator
    {
        yield 'no mail address' => [false, null, false, 0];
        yield 'given mail addresses' => [true, ['mailFixture1', 'mailFixture2'], false, 1];
        yield 'given mail addresses but exception' => [true, ['mailFixture1', 'mailFixture2'], true, 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('addSentryHandlerDataProvider')]
    public function testAddSentryHandler(bool $dsnGiven, bool $throwException, int $invocation): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasSentryDsn',
                'getSentryOptions',
                'getLogLevel',
            ])
            ->getMock();
        $configurationMock->method('hasSentryDsn')->willReturn($dsnGiven);
        $throwException ?
            $configurationMock->expects($this->exactly($invocation))
                ->method('getSentryOptions')->willThrowException(new ServiceNotFoundException('excMsg')) :
            $configurationMock->expects($this->exactly($invocation))
                ->method('getSentryOptions')->willReturn([]);
        $configurationMock->expects($this->exactly($throwException ? 0 : $invocation * 2))
            ->method('getLogLevel')->willReturn('error');

        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addOtherHandler'])
            ->getMock();
        $factoryMock->expects($this->exactly($throwException ? 0 : $invocation * 2))->method('addOtherHandler');

        $sut = new MonologLoggerFactory(
            $configurationMock,
            $validatorMock,
            LoggerFactory::create()
        );

        $this->callMethod($sut, 'addSentryHandler', [$factoryMock]);

        if ($throwException) {
            $this->assertStringContainsString(
                'excMsg',
                file_get_contents($this->logFile)
            );
        }
    }

    public static function addSentryHandlerDataProvider(): Generator
    {
        yield 'no dsn' => [false, false, 0];
        yield 'given dsn' => [true, false, 1];
        yield 'given dsn but exception' => [true, true, 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('addHttpApiHandlerDataProvider')]
    public function testAddHttpApiHandler(bool $addressGiven, $address, bool $throwException, int $invocation): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasHttpApiEndpoint',
                'getHttpApiEndpoint',
                'getHttpApiKey',
                'getLogLevel',
            ])
            ->getMock();
        $configurationMock->method('hasHttpApiEndpoint')->willReturn($addressGiven);
        $configurationMock->expects($this->exactly($invocation))
            ->method('getHttpApiEndpoint')->willReturn($address);
        $throwException ?
            $configurationMock->expects($this->exactly($invocation))
                ->method('getHttpApiKey')->willThrowException(new InvalidArgumentException('excMsg')) :
            $configurationMock->expects($this->exactly($invocation))
                ->method('getHttpApiKey')->willReturn('apiKey');
        $configurationMock->expects($this->exactly($throwException ? 0 : $invocation))
            ->method('getLogLevel')->willReturn('error');

        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addOtherHandler'])
            ->getMock();
        $factoryMock->expects($this->exactly($throwException ? 0 : $invocation))->method('addOtherHandler');

        $sut = new MonologLoggerFactory(
            $configurationMock,
            $validatorMock,
            LoggerFactory::create()
        );

        $this->callMethod($sut, 'addHttpApiHandler', [$factoryMock]);

        if ($throwException) {
            $this->assertStringContainsString(
                'excMsg',
                file_get_contents($this->logFile)
            );
        }
    }

    public static function addHttpApiHandlerDataProvider(): Generator
    {
        yield 'no mail address' => [false, null, false, 0];
        yield 'given mail addresses' => [true, 'endpoint fixture', false, 1];
        yield 'given mail addresses but exception' => [true, 'endpoint fixture', true, 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('addProcessorsDataProvider')]
    public function testAddProcessors(bool $throwException): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addUidProcessor', 'addOtherProcessor'])
            ->getMock();
        $factoryMock->expects(self::atLeastOnce())->method('addUidProcessor');
        $throwException ?
            $factoryMock->expects(self::atLeast(1))->method('addOtherProcessor')
                ->willThrowException(new InvalidArgumentException('excMsg')) :
            $factoryMock->expects(self::atLeast(2))->method('addOtherProcessor');

        $sut = new MonologLoggerFactory(
            $configurationMock,
            $validatorMock,
            $factoryMock
        );

        $this->callMethod($sut, 'addProcessors', [$factoryMock]);

        if ($throwException) {
            $this->assertStringContainsString(
                'excMsg',
                file_get_contents($this->logFile)
            );
        }
    }

    public static function addProcessorsDataProvider(): Generator
    {
        yield 'do not throw exception' => [false];
        yield 'throw exception' => [true];
    }
}
