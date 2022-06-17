<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Config;

use Spiral\Core\InjectableConfig;

final class SignedUrlsConfig extends InjectableConfig
{
    public const CONFIG = 'signed-urls';

    public function getKey(): string
    {
        return $this->config['key'] ?: '';
    }

    public function getAlgo(): string
    {
        return $this->config['algo'] ?: 'sha256';
    }
}
