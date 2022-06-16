<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Bootloader\Security\EncrypterBootloader;
use Spiral\Encrypter\Config\EncrypterConfig;
use Spiral\Router\RouterInterface;
use Spiral\SignedUrls\UrlGenerator;
use Spiral\SignedUrls\UrlGeneratorInterface;

class SignedUrlsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        EncrypterBootloader::class,
        RouterBootloader::class,
    ];

    protected const SINGLETONS = [
        UrlGeneratorInterface::class => [self::class, ['initUrlGenerator']],
    ];

    private function initUrlGenerator(
        RouterInterface $router,
        EncrypterConfig $config
    ): UrlGeneratorInterface {
        return new UrlGenerator($router, $config->getKey());
    }
}
