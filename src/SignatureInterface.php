<?php

declare(strict_types=1);

namespace Spiral\SignedUrls;

interface SignatureInterface
{
    /**
     * Generate a signature for a given string
     */
    public function generate(string $string): string;

    /**
     * Compare a signature and a given string
     */
    public function compare(string $hash, string $string): bool;
}
