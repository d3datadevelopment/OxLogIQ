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
use D3\OxLogIQ\Processors\SessionIdProcessor;
use D3\OxLogIQ\Providers\SessionIdProcessorProvider;
use D3\OxLogIQ\Providers\UidProcessorProvider;
use D3\TestingTools\Development\CanAccessRestricted;
use Exception;
use Generator;
use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
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
                []
            ]
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getFactoryDataProvider')]
    public function testGetFactory($throwException): void
    {
        $providerMock1 = $this->getMockBuilder(SessionIdProcessorProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['register'])
            ->getMock();
        $providerMock1->expects(self::once())->method('register');

        $providerMock2 = $this->getMockBuilder(UidProcessorProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['register'])
            ->getMock();
        $throwException ?
            $providerMock2->expects(self::once())->method('register')->willThrowException(new Exception('excMsg')) :
            $providerMock2->expects(self::once())->method('register');

        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorMock = $this->getMockBuilder(LoggerConfigurationValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sut = oxNew(
            MonologLoggerFactory::class,
            $configurationMock,
            $validatorMock,
            $factoryMock,
            [$providerMock1, $providerMock2]
        );

        self::assertInstanceOf(
            LoggerFactory::class,
            $this->callMethod($sut, 'getFactory')
        );

        if ($throwException) {
            $this->assertStringContainsString(
                'excMsg',
                file_get_contents($this->logFile)
            );
        }
    }

    public static function getFactoryDataProvider(): Generator
    {
        yield 'do not throw exception' => [false, 1];
        yield 'throw exception' => [true, 0];
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
            ->setConstructorArgs([$configurationMock, $validatorMock, $factoryMock, []])
            ->onlyMethods(['getFactory'])
            ->getMock();
        $sut->method('getFactory')->willReturn($factoryMock);

        $this->callMethod($sut, 'create');
    }

}
