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
use D3\OxLogIQ\Providers\IntrospectionProcessorProvider;
use D3\TestingTools\Development\CanAccessRestricted;
use Monolog\Processor\IntrospectionProcessor;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(IntrospectionProcessorProvider::class, 'register')]
class IntrospectionProcessorProviderTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testRegister(): void
    {
        $sut = oxNew(IntrospectionProcessorProvider::class);

        $factoryMock = $this->getMockBuilder(LoggerFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addOtherProcessor'])
            ->getMock();
        $factoryMock->expects(self::once())->method('addOtherProcessor')->with(
            $this->isInstanceOf(IntrospectionProcessor::class)
        );

        $this->callMethod($sut, 'register', [$factoryMock]);
    }
}
