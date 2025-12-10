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

use D3\OxLogIQ\ShutdownActiveModulesDataProviderBridge;
use D3\TestingTools\Development\CanAccessRestricted;
use Generator;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ActiveModulesDataProviderBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ActiveModulesDataProviderInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Exception as MockObjectException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;

#[Small]
#[RunTestsInSeparateProcesses]
#[CoversMethod(ShutdownActiveModulesDataProviderBridge::class, 'handleShutdown')]
#[CoversMethod(ShutdownActiveModulesDataProviderBridge::class, 'getLogger')]
#[CoversMethod(ShutdownActiveModulesDataProviderBridge::class, 'getHandledErrors')]
#[CoversMethod(ShutdownActiveModulesDataProviderBridge::class, 'getModuleIds')]
#[CoversMethod(ShutdownActiveModulesDataProviderBridge::class, 'getModulePaths')]
#[CoversMethod(ShutdownActiveModulesDataProviderBridge::class, 'getControllers')]
#[CoversMethod(ShutdownActiveModulesDataProviderBridge::class, 'getClassExtensions')]
class ShutdownActiveModulesDataProviderBridgeTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws MockObjectException
     * @throws ReflectionException
     */
    #[Test]
    public function testHandleShutdownWithError(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->once())
            ->method('critical')
            ->with($this->callback(function ($message) {
                return str_contains($message, '[uncaught error]') &&
                    str_contains($message, 'Test error');
            }));

        $sut = $this->getMockBuilder(ShutdownActiveModulesDataProviderBridge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHandledErrors', 'getLogger'])
            ->getMock();
        $sut->method('getHandledErrors')->willReturn([E_USER_WARNING]);
        $sut->method('getLogger')->willReturn($loggerMock);

        error_clear_last();
        @trigger_error("Test error", E_USER_WARNING);

        $this->callMethod(
            $sut,
            'handleShutdown'
        );
    }

    /**
     * @throws MockObjectException
     * @throws ReflectionException
     */
    #[Test]
    public function testHandleShutdownWithoutError(): void
    {
        $loggerMock = $this->createMock(LoggerInterface::class);
        $loggerMock->expects($this->never())
            ->method('critical')
            ->with($this->callback(function ($message) {
                return str_contains($message, '[uncaught error]') &&
                    str_contains($message, 'Test error');
            }));

        $sut = $this->getMockBuilder(ShutdownActiveModulesDataProviderBridge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHandledErrors', 'getLogger'])
            ->getMock();
        $sut->method('getHandledErrors')->willReturn([E_USER_NOTICE]);
        $sut->method('getLogger')->willReturn($loggerMock);

        error_clear_last();
        @trigger_error("Test error", E_USER_WARNING);

        $this->callMethod(
            $sut,
            'handleShutdown'
        );
    }

    #[Test]
    public function testGetLogger(): void
    {
        $innerBridge = $this->createMock(ActiveModulesDataProviderBridgeInterface::class);
        $provider = $this->createMock(ActiveModulesDataProviderInterface::class);
        $sut = new ShutdownActiveModulesDataProviderBridge($innerBridge, $provider);

        $this->assertInstanceOf(LoggerInterface::class, $sut->getLogger());
    }

    #[Test]
    public function testGetHandledErrors(): void
    {
        $innerBridge = $this->createMock(ActiveModulesDataProviderBridgeInterface::class);
        $provider = $this->createMock(ActiveModulesDataProviderInterface::class);
        $sut = new ShutdownActiveModulesDataProviderBridge($innerBridge, $provider);

        $errorList = $sut->getHandledErrors();
        $this->assertIsArray($errorList);
        $this->assertContains(E_ERROR, $errorList);
    }

    /**
     * @throws MockObjectException
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('parentMethodsDataProvider')]
    public function testParentMethods(string $methodName): void
    {
        $innerBridge = $this->createMock(ActiveModulesDataProviderBridgeInterface::class);
        $innerBridge->expects(self::once())
            ->method($methodName);
        $provider = $this->createMock(ActiveModulesDataProviderInterface::class);

        $sut = new ShutdownActiveModulesDataProviderBridge($innerBridge, $provider);

        $this->callMethod(
            $sut,
            $methodName
        );
    }

    public static function parentMethodsDataProvider(): Generator
    {
        yield ['getModuleIds'];
        yield ['getModulePaths'];
        yield ['getControllers'];
        yield ['getClassExtensions'];
    }
}
