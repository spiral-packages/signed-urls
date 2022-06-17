<?php

namespace Spiral\SignedUrls\Tests;

use Mockery as m;
use Spiral\Core\Container;

class TestCase extends \Spiral\Testing\TestCase
{
    protected function setUp(): void
    {
        $this->beforeInit(function (Container $container) {
            $container->bind(
                \Psr\Http\Message\UriFactoryInterface::class,
                m::mock(\Psr\Http\Message\UriFactoryInterface::class)
            );
        });

        parent::setUp();


    }

    public function rootDirectory(): string
    {
        return __DIR__.'/../';
    }

    public function defineBootloaders(): array
    {
        return [
            \Spiral\Boot\Bootloader\ConfigurationBootloader::class,
            \Spiral\SignedUrls\Bootloader\SignedUrlsBootloader::class,
        ];
    }
}
