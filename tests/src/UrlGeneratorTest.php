<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Tests;

use Mockery as m;
use Nyholm\Psr7\Uri;
use Spiral\Router\RouterInterface;
use Spiral\SignedUrls\SignatureInterface;
use Spiral\SignedUrls\UrlGenerator;

final class UrlGeneratorTest extends TestCase
{
    private m\LegacyMockInterface|m\MockInterface|RouterInterface $router;
    private SignatureInterface|m\LegacyMockInterface|m\MockInterface $signature;
    private UrlGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->router = m::mock(RouterInterface::class);
        $this->signature = m::mock(SignatureInterface::class);
        $this->generator = new UrlGenerator($this->router, $this->signature);
    }

    public function testSignUrlWithoutExpiration(): void
    {
        $this->signature->shouldReceive('generate')->once()->with('/category/1?bar=baz')
            ->andReturn('hashed_string');

        $uri = $this->generator->signedUrl(new Uri('http://site.com/category/1?bar=baz'));

        $this->assertSame(
            'http://site.com/category/1?bar=baz&signature=hashed_string',
            (string)$uri
        );
    }

    public function testSignUrlWithExpiration(): void
    {
        $expiration = new \DateTime('2011-01-01T15:03:01.012345Z');

        $this->signature->shouldReceive('generate')->once()
            ->with('/category/1?bar=baz&expires='.$expiration->getTimestamp())
            ->andReturn('hashed_string');

        $uri = $this->generator->signedUrl(new Uri('http://site.com/category/1?bar=baz'), $expiration);

        $this->assertSame(
            'http://site.com/category/1?bar=baz&expires=1293894181&signature=hashed_string',
            (string)$uri
        );
    }

    public function testSignRouteWithoutExpirationWithQueryString(): void
    {
        $this->router->shouldReceive('uri')->once()->with('foo', ['bar' => 'baz', 'zoo' => 'zaz'])
            ->andReturn(new Uri('http://site.com/category/1?bar=baz'));

        $this->signature->shouldReceive('generate')->once()->with('/category/1?bar=baz')
            ->andReturn('hashed_string');

        $uri = $this->generator->signedRoute('foo', ['zoo' => 'zaz', 'bar' => 'baz']);

        $this->assertSame(
            'http://site.com/category/1?bar=baz&signature=hashed_string',
            (string)$uri
        );
    }

    public function testSignedRouteWithExpiration(): void
    {
        $expiration = new \DateTime('2011-01-01T15:03:01.012345Z');

        $this->router->shouldReceive('uri')->once()
            ->with('foo', ['bar' => 'baz', 'zoo' => 'zaz'])
            ->andReturn(new Uri('http://site.com/category/1?bar=baz'));

        $this->signature->shouldReceive('generate')
            ->once()
            ->with('/category/1?bar=baz&expires='.$expiration->getTimestamp())
            ->andReturn('hashed_string');

        $uri = $this->generator->signedRoute('foo', ['zoo' => 'zaz', 'bar' => 'baz'], $expiration);

        $this->assertSame(
            'http://site.com/category/1?bar=baz&expires=1293894181&signature=hashed_string',
            (string)$uri
        );
    }

    public function testValidateSignatureWithSignatureAndExpiredTimestamp(): void
    {
        $this->signature->shouldReceive('compare')
            ->once()
            ->with('hashed_string', '/category/1?bar=baz&expires=1293894181&page=1')
            ->andReturnTrue();

        $this->assertFalse(
            $this->generator->hasValidSignature(
                new Uri(
                    'http://site.com/category/1?bar=baz&expires=1293894181&signature=hashed_string&page=1'
                )
            )
        );
    }

    public function testValidateSignatureWithSignatureAndNotExpiredTimestamp(): void
    {
        $timestamp = (new \DateTime)->add(\DateInterval::createFromDateString('+1 second'))->getTimestamp();

        $this->signature->shouldReceive('compare')
            ->once()
            ->with('hashed_string', '/category/1?bar=baz&expires='.$timestamp.'&page=1')
            ->andReturnTrue();

        $this->assertTrue(
            $this->generator->hasValidSignature(
                new Uri(
                    'http://site.com/category/1?bar=baz&expires='.$timestamp.'&signature=hashed_string&page=1'
                )
            )
        );
    }

    public function testValidateInvalidSignatureWithSignatureAndNotExpiredTimestamp(): void
    {
        $timestamp = (new \DateTime)->add(\DateInterval::createFromDateString('+1 second'))->getTimestamp();

        $this->signature->shouldReceive('compare')
            ->once()
            ->with('hashed_string', '/category/1?bar=baz&expires='.$timestamp.'&page=1')
            ->andReturnFalse();

        $this->assertFalse(
            $this->generator->hasValidSignature(
                new Uri(
                    'http://site.com/category/1?bar=baz&expires='.$timestamp.'&signature=hashed_string&page=1'
                )
            )
        );
    }

    public function testValidateSignatureWithoutQueryString(): void
    {
        $this->signature->shouldReceive('compare')
            ->once()
            ->with('hashed_string', '/category/1')
            ->andReturnFalse();

        $this->assertFalse(
            $this->generator->hasValidSignature(
                new Uri(
                    'http://site.com/category/1?signature=hashed_string'
                )
            )
        );
    }

    public function testValidateSignatureWithoutSignature(): void
    {
        $this->signature->shouldReceive('compare')
            ->once()
            ->with('', '/category/1?bar=baz&expires=1293894181&page=1')
            ->andReturnFalse();

        $this->assertFalse(
            $this->generator->hasValidSignature(
                new Uri(
                    'http://site.com/category/1?bar=baz&expires=1293894181&page=1'
                )
            )
        );
    }
}
