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
        private readonly SignatureInterface $signature
    ) {
    }

    public function signedRoute(
        string $route,
        array $parameters = [],
        \DateTimeInterface|\DateInterval|null $expiration = null
    ): UriInterface {
        return $this->signedUrl(
            $this->router->uri($route, $parameters),
            $expiration
        );
    }

    public function signedUrl(
        UriInterface $uri,
        \DateTimeInterface|\DateInterval|null $expiration = null
    ): UriInterface {
        \parse_str($uri->getQuery(), $parameters);

        $parameters = $this->prepareParameters($parameters, $expiration);

        return $uri->withQuery(\http_build_query(
            \array_merge($parameters, [
                'signature' => $this->signature->generate($this->prepareUri($uri)),
            ])
        ));
    }

    public function hasValidSignature(UriInterface $uri): bool
    {
        return $this->hasCorrectSignature($uri) && $this->signatureHasNotExpired($uri);
    }

    public function hasCorrectSignature(UriInterface $uri): bool
    {
        $url = $this->prepareUri($uri);

        $url = \ltrim(\preg_replace('/(^|&|\?)signature=[^&]+/', '', $url), '&');

        return $this->signature->compare(
            $this->getSignatureFromQueryString($uri),
            \rtrim($url, '?')
        );
    }

    public function signatureHasNotExpired(UriInterface $uri): bool
    {
        \parse_str($uri->getQuery(), $output);

        $expires = (int)($output['expires'] ?? null);

        if ($expires === 0) {
            return true;
        }

        return (new \DateTime)->getTimestamp() <= $expires;
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
        if (\array_key_exists('signature', $parameters)) {
            throw new ReservedParameterException(
                '"Signature" is a reserved parameter when generating signed routes. Please rename your parameter.'
            );
        }

        if (\array_key_exists('expires', $parameters)) {
            throw new ReservedParameterException(
                '"Expires" is a reserved parameter when generating signed routes. Please rename your parameter.'
            );
        }
    }

    private function prepareParameters(
        array $parameters,
        \DateInterval|\DateTimeInterface|null $expiration
    ): array {
        $this->ensureSignedRouteParametersAreNotReserved($parameters);

        if ($expiration !== null) {
            $parameters = \array_merge($parameters, ['expires' => $this->toTimestamp($expiration)]);
        }

        \ksort($parameters);

        return $parameters;
    }

    private function prepareUri(UriInterface $uri): string
    {
        return (string)$uri->withScheme('')->withHost('');
    }
}
