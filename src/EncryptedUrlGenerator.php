<?php

declare(strict_types=1);

namespace Spiral\SignedUrls;

use Psr\Http\Message\UriInterface;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Encrypter\Exception\DecryptException;
use Spiral\Router\RouterInterface;
use Spiral\SignedUrls\Exception\InvalidSignatureException;

final class EncryptedUrlGenerator extends UrlGenerator implements EncryptedUrlGeneratorInterface
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly SignatureInterface $signature,
        private readonly EncrypterInterface $encrypter
    ) {
        parent::__construct($router, $signature);
    }

    public function signedUrl(
        UriInterface $uri,
        \DateTimeInterface|\DateInterval|null $expiration = null
    ): UriInterface {
        $uri = parent::signedUrl($uri, $expiration);

        return $uri->withQuery(
            \http_build_query([
                'signature' => $this->encrypter->encrypt($uri->getQuery()),
            ])
        );
    }

    public function hasValidSignature(UriInterface $uri): bool
    {
        return $this->hasCorrectSignature($uri) && $this->signatureHasNotExpired($uri);
    }

    public function hasCorrectSignature(UriInterface $uri): bool
    {
        try {
            return parent::hasCorrectSignature($this->getDecryptedUri($uri));
        } catch (InvalidSignatureException $e) {
            return false;
        }
    }

    public function signatureHasNotExpired(UriInterface $uri): bool
    {
        try {
            return parent::signatureHasNotExpired($this->getDecryptedUri($uri));
        } catch (InvalidSignatureException $e) {
            return false;
        }
    }

    private function getDecryptedUri(UriInterface $uri): UriInterface
    {
        \parse_str($uri->getQuery(), $parameters);

        if (! isset($parameters['signature'])) {
            throw new InvalidSignatureException();
        }

        try {
            $query = $this->encrypter->decrypt($parameters['signature']);
            return $uri->withQuery($query);
        } catch (DecryptException $e) {
            throw new InvalidSignatureException();
        }
    }
}
