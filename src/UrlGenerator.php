<?php

declare(strict_types=1);

namespace Spiral\SignedUrls;

use Psr\Http\Message\UriInterface;
use Spiral\Router\RouterInterface;
use Spiral\SignedUrls\Exception\ReservedParameterException;

final class UrlGenerator implements UrlGeneratorInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly string $key
    ) {
    }

    public function signedRoute(
        string $route,
        iterable $parameters = [],
        \DateTimeInterface|\DateInterval|null $expiration = null
    ): UriInterface {
        $this->ensureSignedRouteParametersAreNotReserved($parameters);

        if ($expiration !== null) {
            $parameters = $parameters + ['expires' => $this->toTimestamp($expiration)];
        }

        ksort($parameters);

        return $this->router->uri(
            $route,
            $parameters + [
                'signature' => hash_hmac('sha256', (string)$this->router->uri($route, $parameters), $this->key),
            ]
        );
    }

    public function temporarySignedRoute(
        string $route,
        \DateTimeInterface|\DateInterval $expiration,
        iterable $parameters = [],
    ): UriInterface {
        return $this->signedRoute($route, $parameters, $expiration);
    }

    public function hasValidSignature(UriInterface $uri): bool
    {
        return $this->hasCorrectSignature($uri) && $this->signatureHasNotExpired($uri);
    }

    public function hasCorrectSignature(UriInterface $uri): bool
    {
        $url = (string)$uri;

        $url = ltrim(preg_replace('/(^|&)signature=[^&]+/', '', $url), '&');

        $signature = hash_hmac('sha256', rtrim($url, '?'), $this->key);

        return hash_equals($signature, $this->getSignatureFromQueryString($uri));
    }

    public function signatureHasNotExpired(UriInterface $uri): bool
    {
        \parse_str($uri->getQuery(), $output);

        $expires = $output['expires'] ?? null;

        return ! ($expires && (new \DateTime)->getTimestamp() > $expires);
    }

    private function getSignatureFromQueryString(UriInterface $uri): string
    {
        \parse_str($uri->getQuery(), $output);

        return (string)($output['signature'] ?? '');
    }

    /**
     * Get the "available at" UNIX timestamp.
     */
    private function toTimestamp(\DateTimeInterface|\DateInterval $delay): int
    {
        if ($delay instanceof \DateInterval) {
            $delay = (new \DateTime)->add($delay);
        }

        return $delay->getTimestamp();
    }

    /**
     * Ensure the given signed route parameters are not reserved.
     *
     * @throws ReservedParameterException
     */
    private function ensureSignedRouteParametersAreNotReserved(array $parameters): void
    {
        if (array_key_exists('signature', $parameters)) {
            throw new ReservedParameterException(
                '"Signature" is a reserved parameter when generating signed routes. Please rename your parameter.'
            );
        }

        if (array_key_exists('expires', $parameters)) {
            throw new ReservedParameterException(
                '"Expires" is a reserved parameter when generating signed routes. Please rename your parameter.'
            );
        }
    }
}
