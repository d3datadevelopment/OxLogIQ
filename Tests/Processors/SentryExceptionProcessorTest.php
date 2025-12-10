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

namespace D3\OxLogIQ\Tests\Processors;

use D3\OxLogIQ\Processors\SentryExceptionProcessor;
use D3\TestingTools\Development\CanAccessRestricted;
use Exception;
use Generator;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(SentryExceptionProcessor::class, '__invoke')]
#[CoversMethod(SentryExceptionProcessor::class, 'checkForException')]
class SentryExceptionProcessorTest extends TestCase
{
    use CanAccessRestricted;

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('invokeDataProvider')]
    public function testInvoke(bool $needMoveCheck, $record, $expected): void
    {
        $sut = $this->getMockBuilder(SentryExceptionProcessor::class)
            ->onlyMethods(['checkForException'])
            ->getMock();
        $sut->method('checkForException')->willReturn($needMoveCheck);

        $this->assertEquals(
            $expected,
            $this->callMethod($sut, '__invoke', [$record])
        );
    }

    public static function invokeDataProvider(): Generator
    {
        yield 'check failed' => [false,
            ['level' => Logger::ERROR, 'context' => ['other' => new Exception()]],
            ['level' => Logger::ERROR, 'context' => ['other' => new Exception()]],
        ];
        yield 'check passed, exception moved' => [true,
            ['level' => Logger::ERROR, 'context' => ['other' => new Exception()]],
            ['level' => Logger::ERROR, 'context' => ['exception' => new Exception()]],
        ];
        yield 'check passed, exception kept' => [true,
            ['level' => Logger::ERROR, 'context' => ['exception' => new Exception()]],
            ['level' => Logger::ERROR, 'context' => ['exception' => new Exception()]],
        ];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('checkForExceptionDataProvider')]
    public function testCheckForException($record, $expected): void
    {
        $sut = new SentryExceptionProcessor();
        $this->assertSame(
            $expected,
            $this->callMethod($sut, 'checkForException', [$record])
        );
    }

    public static function checkForExceptionDataProvider(): Generator
    {
        yield 'no level'    => [['foo' => 'bar'], false];
        yield 'level below error'   => [['level' => Logger::INFO], false];
        yield 'level is error but no context'   => [['level' => Logger::ERROR], false];
        yield 'level is error but exception'   => [['level' => Logger::ERROR, 'context' => ['exception' => 'excMsg']], false];
        yield 'need exception search' => [['level' => Logger::ERROR, 'context' => ['other' => null]], true];
    }
}
