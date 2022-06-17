<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Tests\Bootloader;

use Spiral\SignedUrls\Config\SignedUrlsConfig;
use Spiral\SignedUrls\HmacSignature;
use Spiral\SignedUrls\SignatureInterface;
use Spiral\SignedUrls\Tests\TestCase;
use Spiral\SignedUrls\UrlGenerator;
use Spiral\SignedUrls\UrlGeneratorInterface;

final class SignedUrlsBootloaderTest extends TestCase
{
    public const ENV = [
        'SIGNED_URLS_KEY' => 'secret',
        'SIGNED_URLS_ALGO' => 'md5'
    ];

    public function testUrlGenerator(): void
    {
        $this->assertContainerBoundAsSingleton(UrlGeneratorInterface::class, UrlGenerator::class);
    }

    public function testSignature(): void
    {
        $this->assertContainerBoundAsSingleton(SignatureInterface::class, HmacSignature::class);
    }

    public function testConfig(): void
    {
        $this->assertConfigMatches(SignedUrlsConfig::CONFIG, [
            'key' => 'secret',
            'algo' => 'md5'
        ]);
    }
}
