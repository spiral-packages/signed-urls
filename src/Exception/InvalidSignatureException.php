<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Exception;

use Spiral\Http\Exception\ClientException;

class InvalidSignatureException extends ClientException
{
    public function __construct(string $message = 'Invalid signature.')
    {
        parent::__construct(403, $message);
    }
}
