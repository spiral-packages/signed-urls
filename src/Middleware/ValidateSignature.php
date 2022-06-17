<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\SignedUrls\Exception\InvalidSignatureException;
use Spiral\SignedUrls\UrlGeneratorInterface;

final class ValidateSignature implements MiddlewareInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $urls
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->urls->hasValidSignature($request->getUri())) {
            return $handler->handle($request);
        }

        throw new InvalidSignatureException();
    }
}
