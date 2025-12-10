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

namespace D3\OxLogIQ\Tests\Release;

use D3\OxLogIQ\Release\ReleaseService;
use D3\TestingTools\Development\CanAccessRestricted;
use DateTime;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[Small]
#[CoversMethod(ReleaseService::class, 'getRelease')]
#[CoversMethod(ReleaseService::class, 'getFilePath')]

class ReleaseServiceTest extends TestCase
{
    use CanAccessRestricted;

    protected $logFile = __DIR__ . '/test-error.log';

    public function setUp(): void
    {
        parent::setUp();

        touch($this->logFile);
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
    public function testGetRelease(): void
    {
        $sut = $this->getMockBuilder(ReleaseService::class)
            ->onlyMethods(['getFilePath'])
            ->getMock();
        $sut->method('getFilePath')->willReturn($this->logFile);

        $date = new DateTime();
        $date->modify('-1 seconds');

        $this->assertContains(
            $this->callMethod($sut, 'getRelease'),
            [
                date('Y-m-d_H:i:s', time()),
                $date->format('Y-m-d'),
            ],
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetReleaseFileMissing(): void
    {
        $sut = $this->getMockBuilder(ReleaseService::class)
            ->onlyMethods(['getFilePath'])
            ->getMock();
        $sut->method('getFilePath')->willReturn($this->logFile.'_missing');

        $this->assertSame('', $this->callMethod($sut, 'getRelease'));
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetFilePath(): void
    {
        $sut = new ReleaseService();

        $fileName = $this->callMethod($sut, 'getFilePath');

        $this->assertStringContainsString('composer.lock', $fileName);
        $this->assertFileExists($fileName);
    }
}
