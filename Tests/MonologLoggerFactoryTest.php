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
use D3\OxLogIQ\Core\FallbackLogger;
use D3\OxLogIQ\Handlers\FallbackHandler;
use D3\OxLogIQ\MonologConfiguration;
use D3\OxLogIQ\MonologLoggerFactory;
use D3\OxLogIQ\Providers\Processors\SessionIdProcessorProvider;
use D3\OxLogIQ\Providers\Processors\UidProcessorProvider;
use D3\TestingTools\Development\CanAccessRestricted;
use Exception;
use Generator;
use LogicException;
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
#[CoversMethod(MonologLoggerFactory::class, 'getFactory')]
#[CoversMethod(MonologLoggerFactory::class, 'create')]
#[CoversMethod(MonologLoggerFactory::class, 'checkProviderClass')]
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

        $fallbackLogger  = new FallbackLogger(new FallbackHandler());

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
                [],
                $fallbackLogger,
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getFactoryDataProvider')]
    public function testGetFactory($throwProviderClassException, $throwProvideException, $invocationCount): void
    {
        $providerMock1 = $this->getMockBuilder(SessionIdProcessorProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['provide'])
            ->getMock();
        $providerMock1->expects(self::exactly($invocationCount))->method('provide');

        $providerMock2 = $this->getMockBuilder(UidProcessorProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['provide'])
            ->getMock();
        $throwProvideException ?
            $providerMock2->expects(self::exactly($invocationCount))->method('provide')
                ->willThrowException(new Exception('excMsg')) :
            $providerMock2->expects(self::exactly($invocationCount))->method('provide');

        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fallbackLoggerMock  = $this->getMockBuilder(FallbackLogger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();
        $fallbackLoggerMock->expects(self::atLeast((int) ($throwProvideException || $throwProviderClassException)))
            ->method('get');

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->setConstructorArgs([
                $configurationMock,
                $validatorMock,
                $factoryMock,
                [$providerMock1, $providerMock2],
                $fallbackLoggerMock
            ])
            ->onlyMethods(['checkProviderClass'])
            ->getMock();
        $throwProviderClassException ?
            $sut->method('checkProviderClass')->willThrowException(new Exception('excMsg')) :
            $sut->method('checkProviderClass');

        self::assertInstanceOf(
            LoggerFactory::class,
            $this->callMethod($sut, 'getFactory')
        );
    }

    public static function getFactoryDataProvider(): Generator
    {
        yield 'wrong provider class' => [true, false, 0];
        yield 'do not throw exception' => [false, false, 1];
        yield 'throw provider exception' => [false, true, 1];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('createDataProvider')]
    public function testCreate(?array $handlers, string $expectedName): void
    {
        $loggerMock = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getName', 'getHandlers'])
            ->getMock();
        $loggerMock->method('getName')->willReturn('defaultLogger');
        $loggerMock->method('getHandlers')->willReturn($handlers);

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
        $factoryMock->expects(self::once())->method('build')->willReturn($loggerMock);

        $fallbackLogger  = new FallbackLogger(new FallbackHandler());

        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->setConstructorArgs([$configurationMock, $validatorMock, $factoryMock, [], $fallbackLogger])
            ->onlyMethods(['getFactory'])
            ->getMock();
        $sut->method('getFactory')->willReturn($factoryMock);

        $logger = $this->callMethod($sut, 'create');

        $this->assertStringContainsString(
            $expectedName,
            $logger->getName()
        );
    }

    public static function createDataProvider(): Generator
    {
        yield 'null handlers'   => [null, 'Fallback'];
        yield 'empty handlers'   => [[], 'Fallback'];
        yield 'avaiblable handlers'   => [['fixture'], 'defaultLogger'];
    }

    /**
     * @throws ReflectionException
     * @dataProvider checkProviderClassExceptionProvider
     */
    #[Test]
    #[DataProvider('checkProviderClassExceptionProvider')]
    public function testCheckProviderClassPassed($instance, $expectException): void
    {
        $sut = $this->getMockBuilder(MonologLoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getFactory'])
            ->getMock();

        try {
            $this->callMethod($sut, 'checkProviderClass', [$instance]);
            $this->assertFalse($expectException);
        } catch (LogicException) {
            $this->assertTrue($expectException);
        }
    }

    public static function checkProviderClassExceptionProvider(): Generator
    {
        yield 'provider implements interface' => [new UidProcessorProvider(), false];
        yield 'interface missing'             => [new Exception(), true];
    }
}
