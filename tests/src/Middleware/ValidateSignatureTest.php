<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Tests\Middleware;

use Mockery as m;
use Nyholm\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\SignedUrls\Exception\InvalidSignatureException;
use Spiral\SignedUrls\Middleware\ValidateSignature;
use Spiral\SignedUrls\UrlGeneratorInterface;

final class ValidateSignatureTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private UrlGeneratorInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface $urls;
    private ValidateSignature $middleware;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urls = m::mock(UrlGeneratorInterface::class);
        $this->middleware = new ValidateSignature($this->urls);
    }

    public function testValidSignatureShouldBeHandled(): void
    {
        $request = m::mock(ServerRequestInterface::class);
        $response = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = new Uri());
        $this->urls->shouldReceive('hasValidSignature')->once()->with($uri)->andReturnTrue();
        $response->shouldReceive('handle')->once()->with($request);

        $this->middleware->process($request, $response);
    }


    public function testInvalidSignatureShouldThrowAnException(): void
    {
        $this->expectException(InvalidSignatureException::class);

        $request = m::mock(ServerRequestInterface::class);
        $response = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = new Uri());
        $this->urls->shouldReceive('hasValidSignature')->once()->with($uri)->andReturnFalse();

        $this->middleware->process($request, $response);
    }
}
