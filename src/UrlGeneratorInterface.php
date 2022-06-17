<?php

declare(strict_types=1);

namespace Spiral\SignedUrls;

use Psr\Http\Message\UriInterface;
use Spiral\SignedUrls\Exception\ReservedParameterException;

interface UrlGeneratorInterface
{
    /**
     * Create a signed route URL for a named route.
     *
     * @throws ReservedParameterException
     */
    public function signedRoute(
        string $route,
        array $parameters = [],
        \DateTimeInterface|\DateInterval|null $expiration = null
    ): UriInterface;

    /**
     * Create a signed URL for a given Uri.
     */
    public function signedUrl(UriInterface $uri): UriInterface;

    /**
     * Determine if the given request has a valid signature and not expired.
     */
    public function hasValidSignature(UriInterface $uri): bool;

    /**
     * Determine if the signature from the given request matches the URL.
     */
    public function hasCorrectSignature(UriInterface $uri): bool;

    /**
     * Determine if the expires timestamp from the given request is not from the past.
     */
    public function signatureHasNotExpired(UriInterface $uri): bool;
}
