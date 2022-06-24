<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Router\RouterInterface;
use Spiral\SignedUrls\Config\SignedUrlsConfig;
use Spiral\SignedUrls\EncryptedUrlGenerator;
use Spiral\SignedUrls\EncryptedUrlGeneratorInterface;
use Spiral\SignedUrls\HmacSignature;
use Spiral\SignedUrls\SignatureInterface;
use Spiral\SignedUrls\UrlGenerator;
use Spiral\SignedUrls\UrlGeneratorInterface;

class SignedUrlsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        RouterBootloader::class,
    ];

    protected const SINGLETONS = [
        UrlGeneratorInterface::class => [self::class, 'initUrlGenerator'],
        EncryptedUrlGeneratorInterface::class => [self::class, 'initEncryptedUrlGenerator'],
        SignatureInterface::class => [self::class, 'initSignature'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(SignedUrlsConfig::CONFIG, [
            'key' => $env->get('SIGNED_URLS_KEY'),
            'algo' => $env->get('SIGNED_URLS_ALGO', 'sha256'),
        ]);
    }

    private function initSignature(SignedUrlsConfig $config): SignatureInterface
    {
        return new HmacSignature(
            $config->getKey(), $config->getAlgo()
        );
    }

    private function initUrlGenerator(
        RouterInterface $router,
        SignatureInterface $signature
    ): UrlGeneratorInterface {
        return new UrlGenerator($router, $signature);
    }

    private function initEncryptedUrlGenerator(
        RouterInterface $router,
        SignatureInterface $signature,
        EncrypterInterface $encrypter
    ): UrlGeneratorInterface {
        return new EncryptedUrlGenerator($router, $signature, $encrypter);
    }
}
