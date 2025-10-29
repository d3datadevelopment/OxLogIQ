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

use D3\LoggerFactory\LoggerFactory;
use D3\LoggerFactory\Options\FileLoggerHandlerOption;
use D3\OxLogiQ\MonologConfiguration;
use D3\OxLogiQ\MonologLoggerFactory;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
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

#[Small]
#[CoversMethod(MonologLoggerFactory::class, '__construct')]
#[CoversMethod(MonologLoggerFactory::class, 'create')]
#[CoversMethod(MonologLoggerFactory::class, 'addFileHandler')]
#[CoversMethod(MonologLoggerFactory::class, 'getFormatter')]
#[CoversMethod(MonologLoggerFactory::class, 'addMailHandler')]
#[CoversMethod(MonologLoggerFactory::class, 'addProcessors')]
class MonologLoggerFactoryTest extends TestCase
{
    use CanAccessRestricted;

    protected MonologLoggerFactory $sut;

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
                LoggerFactory::create()
            ]
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
            ->onlyMethods(['validate'])
            ->getMock();
        $validatorMock->expects(self::atLeastOnce())->method('validate');

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['build'])
            ->getMock();
        $factoryMock->method('build');

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->onlyMethods(['addFileHandler', 'addMailHandler', 'addProcessors'])
            ->setConstructorArgs([$configurationMock, $validatorMock, $factoryMock])
            ->getMock();
        $sut->expects(self::once())->method('addFileHandler');
        $sut->expects(self::once())->method('addMailHandler');
        $sut->expects(self::once())->method('addProcessors');

        self::assertInstanceOf(
            Logger::class,
            $this->callMethod($sut, 'create')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testAddFileHandler(): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getLogLevel', 'getLogFilePath', 'getRetentionDays'])
            ->getMock();
        $configurationMock->method('getLogLevel')->willReturn('error');
        $configurationMock->method('getLogFilePath')->willReturn('/var/log/error.log');
        $configurationMock->method('getRetentionDays')->willReturn( 5);

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setValue($sut, 'configuration', $configurationMock);

        $fileHandlerMock = $this->getMockBuilder(StreamHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setFormatter'])
            ->getMock();
        $fileHandlerMock->expects(self::once())->method('setFormatter');

        $fileLoggerHandlerOptionMock = $this->getMockBuilder(FileLoggerHandlerOption::class)
            ->setConstructorArgs(['/var/log/error.log'])
            ->onlyMethods(['getHandler', 'setBuffering'])
            ->getMock();
        $fileLoggerHandlerOptionMock->method('getHandler')->willReturn($fileHandlerMock);
        $fileLoggerHandlerOptionMock->expects(self::once())->method('setBuffering');

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addFileHandler'])
            ->getMock();
        $factoryMock->expects(self::once())->method('addFileHandler')->willReturn($fileLoggerHandlerOptionMock);

        $this->callMethod($sut, 'addFileHandler', [$factoryMock]);
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
    public function testAddMailHandler(bool $addressGiven, $address, int $invocation): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasNotificationMailRecipient',
                'getNotificationMailRecipients',
                'getNotificationMailLevel',
                'getNotificationMailSubject',
                'getNotificationMailFrom',
            ])
            ->getMock();
        $configurationMock->method( 'hasNotificationMailRecipient' )->willReturn( $addressGiven);
        $configurationMock->expects($this->exactly($invocation))
                          ->method( 'getNotificationMailRecipients' )->willReturn( $address);
        $configurationMock->expects($this->exactly($invocation))
                          ->method('getNotificationMailLevel')->willReturn( 'error');
        $configurationMock->expects($this->exactly($invocation))
                          ->method('getNotificationMailSubject')->willReturn( 'mySubject');
        $configurationMock->expects($this->exactly($invocation))
                          ->method('getNotificationMailFrom')->willReturn( 'fromAddress');

        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addMailHandler'])
            ->getMock();
        $factoryMock->expects($this->exactly($invocation))->method('addMailHandler');

        $sut = new MonologLoggerFactory(
            $configurationMock,
            $validatorMock,
            LoggerFactory::create()
        );

        $this->callMethod($sut, 'addMailHandler', [$factoryMock]);
    }

    public static function addMailHandlerDataProvider(): Generator
    {
        yield 'no mail address' => [false, null, 0];
        yield 'given mail addresses' => [true, ['mailFixture1', 'mailFixture2'], 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testAddProcessors(): void
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
        $factoryMock->expects(self::atLeast(2))->method('addOtherProcessor');

        $sut = new MonologLoggerFactory(
            $configurationMock,
            $validatorMock,
            LoggerFactory::create()
        );

        $this->callMethod($sut, 'addProcessors', [$factoryMock]);
    }
}