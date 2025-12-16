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

namespace D3\OxLogIQ\Tests\Providers;

use D3\LoggerFactory\LoggerFactory;
use D3\LoggerFactory\Options\FileLoggerHandlerOption;
use D3\OxLogIQ\MonologConfiguration;
use D3\OxLogIQ\Providers\FileHandlerProvider;
use D3\TestingTools\Development\CanAccessRestricted;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(FileHandlerProvider::class, 'register')]
#[CoversMethod(FileHandlerProvider::class, 'getFormatter')]
class FileHandlerProviderTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testRegister(): void
    {
        $configurationMock = $this->getMockBuilder(MonologConfiguration::class)
            ->disableOriginalConstructor()
            ->onlyMethods([ 'getLogLevel', 'getLogFilePath', 'getRetentionDays' ])
            ->getMock();
        $configurationMock->method('getLogLevel')->willReturn('error');
        $configurationMock->method('getLogFilePath')->willReturn('/var/log/error.log');
        $configurationMock->method('getRetentionDays')->willReturn(5);

        $sut = oxNew(FileHandlerProvider::class, $configurationMock);

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
        $factoryMock->expects(self::once())->method('addFileHandler')
            ->willReturn($fileLoggerHandlerOptionMock);

        $this->callMethod($sut, 'register', [$factoryMock]);
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetFormatter(): void
    {
        $sut = $this->getMockBuilder(FileHandlerProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        self::assertInstanceOf(
            LineFormatter::class,
            $this->callMethod($sut, 'getFormatter')
        );
    }
}
