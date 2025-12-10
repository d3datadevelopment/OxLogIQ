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

use D3\OxLogIQ\Handlers\HttpApiHandler;
use D3\OxLogIQ\Release\ReleaseService;
use D3\OxLogIQ\Release\ReleaseServiceInterface;
use D3\OxLogIQ\Tests\Handlers\Http\ClientStub;
use D3\OxLogIQ\Tests\Handlers\Http\RequestFactoryStub;
use D3\OxLogIQ\Tests\Handlers\Http\ResponseStub;
use D3\TestingTools\Development\CanAccessRestricted;
use DateTime;
use DateTimeImmutable;
use Generator;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\HttpFactory;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use ReflectionException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

#[Small]
#[CoversMethod(HttpApiHandler::class, '__construct')]
#[CoversMethod(HttpApiHandler::class, 'write')]
#[CoversMethod(HttpApiHandler::class, 'getRequest')]
#[CoversMethod(HttpApiHandler::class, 'getData')]
#[CoversMethod(HttpApiHandler::class, 'getRelease')]
#[CoversMethod(HttpApiHandler::class, 'getReleaseService')]
class HttpApiHandlerTest extends TestCase
{
    use CanAccessRestricted;

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
    public function testConstructorWithClient(): void
    {
        $sut = $this->getMockBuilder(HttpApiHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = new ClientStub();
        $request = new RequestFactoryStub();
        $stream = new RequestFactoryStub();

        $this->callMethod(
            $sut,
            '__construct',
            ['endpoint', 'apikey', Logger::DEBUG, $client, $request, $stream]
        );

        $this->assertSame($client, $this->getValue($sut, 'client'));
        $this->assertSame($request, $this->getValue($sut, 'requestFactory'));
        $this->assertSame($stream, $this->getValue($sut, 'streamFactory'));
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('writeDataProvider')]
    public function testWrite(bool $throwException, int $statusCode, ?string $expectedLogText): void
    {
        $request = (new HttpFactory())->createRequest('POST', 'endpoint');

        $responseMock = $this->getMockBuilder(ResponseStub::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getStatusCode'])
            ->getMock();
        $responseMock->method('getStatusCode')->willReturn($statusCode);

        $clientMock = $this->getMockBuilder(ClientStub::class)
            ->onlyMethods(['sendRequest'])
            ->getMock();
        $throwException ?
            $clientMock->expects(self::once())->method('sendRequest')->willThrowException(
                new ClientException('excMsg', $request, $responseMock)
            ) :
            $clientMock->expects(self::once())->method('sendRequest')->willReturn($responseMock);

        $factory = new HttpFactory();

        $sut = $this->getMockBuilder(HttpApiHandler::class)
            ->onlyMethods(['getRequest'])
            ->setConstructorArgs(['endpoint', 'apikey', Logger::DEBUG, $clientMock, $factory, $factory])
            ->getMock();
        $sut->expects(self::once())->method('getRequest')->willReturn($request);

        $this->callMethod(
            $sut,
            'write',
            [[]]
        );

        if ($expectedLogText) {
            $this->assertStringContainsString(
                $expectedLogText,
                file_get_contents($this->logFile)
            );
        }
    }

    public static function writeDataProvider(): Generator
    {
        yield 'throw exception' => [true, 200, 'excMsg'];
        yield 'status 2xx' => [false, 200, null];
        yield 'status 3xx' => [false, 300, null];
        yield 'status 4xx' => [false, 400, '400'];
        yield 'status 5xx' => [false, 500, '500'];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetRequest(): void
    {
        $factory = new HttpFactory();

        $sut = $this->getMockBuilder(HttpApiHandler::class)
            ->setConstructorArgs(['endpoint', 'apikey', Logger::DEBUG, new ClientStub(), $factory, $factory])
            ->onlyMethods(['getData'])
            ->getMock();
        $sut->method('getData')->willReturn(['message' => 'foobar']);

        $request = $this->callMethod(
            $sut,
            'getRequest',
            [[]]
        );

        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertSame('{"message":"foobar"}', (string) $request->getBody());
    }

    /**
     * @param $date
     * @param $expectedDate
     *
     * @return void
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getDataDataProvider')]
    public function testGetData($date, $expectedDate): void
    {
        $sut = $this->getMockBuilder(HttpApiHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRelease'])
            ->getMock();
        $sut->method('getRelease')->willReturn('2025-06-02 15:37:18');

        $data = $this->callMethod(
            $sut,
            'getData',
            [['message' => 'foobar', 'channel' => 'channelFixture', 'datetime' => $date]]
        );

        $this->assertSame('foobar', $data['message']);
        $this->assertSame('channelFixture', $data['log.logger']);
        $this->assertContains($data['@timestamp'], $expectedDate);
        $this->assertSame('2025-06-02 15:37:18', $data['release']);
        $this->assertIsArray($data['call']);
    }

    public static function getDataDataProvider(): Generator
    {
        $date = new DateTimeImmutable();

        yield 'with datetime' => [new DateTime('2015-09-15'), ['2015-09-15T00:00:00+02:00']];
        yield 'without datetime' => [null, [
            $date->format('c'),
            $date->modify('-1 seconds')->format('c'),
            $date->modify('+1 seconds')->format('c'),
        ]];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    #[DataProvider('getReleaseDataProvider')]
    public function testGetRelease(bool $throwException, int $invocationCount, $expected): void
    {
        $releaseServiceMock = $this->getMockBuilder(ReleaseService::class)
            ->onlyMethods(['getRelease'])
            ->getMock();
        $releaseServiceMock->expects(self::exactly($invocationCount))->method('getRelease')->willReturn('2025-06-02_15:37:18');

        $sut = $this->getMockBuilder(HttpApiHandler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getReleaseService'])
            ->getMock();
        $throwException ?
            $sut->method('getReleaseService')->willThrowException(new ServiceNotFoundException('excMsg')) :
            $sut->method('getReleaseService')->willReturn($releaseServiceMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $sut,
                'getRelease',
            )
        );
    }

    public static function getReleaseDataProvider(): Generator
    {
        yield 'throw exception' => [true, 0, ''];
        yield 'no exception' => [false, 1, '2025-06-02_15:37:18'];
    }

    /**
     * @throws ReflectionException
     */
    #[Test]
    public function testGetReleaseService(): void
    {
        $sut = $this->getMockBuilder(HttpApiHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertInstanceOf(
            ReleaseServiceInterface::class,
            $this->callMethod($sut, 'getReleaseService')
        );
    }
}
