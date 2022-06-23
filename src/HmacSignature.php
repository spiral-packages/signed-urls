<?php

declare(strict_types=1);

namespace Spiral\SignedUrls;

final class HmacSignature implements SignatureInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $algo = 'sha256'
    ) {
        if (empty($this->key)) {
            throw new \InvalidArgumentException(
                'You must specify a secret key for generating the HMAC variant of the message digest.'
            );
        }
    }

    public function generate(string $string): string
    {
        return \hash_hmac($this->algo, $string, $this->key);
    }

    public function compare(string $hash, string $string): bool
    {
        return \hash_equals($hash, $this->generate($string));
    }
}
