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

namespace D3\OxLogIQ\Tests\Handlers;

use D3\OxLogIQ\Handlers\FallbackHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Small]
#[CoversMethod(FallbackHandler::class, '__construct')]
class FallbackHandlerTest extends TestCase
{
    #[Test]
    public function testConstruct(): void
    {
        $sut = new FallbackHandler();

        $this->assertInstanceOf(LineFormatter::class, $sut->getFormatter());
        $this->assertStringContainsString('oxideshop.log', $sut->getUrl());
        $this->assertSame(Logger::ERROR, $sut->getLevel());
    }
}