# Signed URL generator for Spiral Framework based on Laravel

[![PHP](https://img.shields.io/packagist/php-v/spiral-packages/signed-urls.svg?style=flat-square)](https://packagist.org/packages/spiral-packages/signed-urls)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/spiral-packages/signed-urls.svg?style=flat-square)](https://packagist.org/packages/spiral-packages/signed-urls)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spiral-packages/signed-urls/run-tests?label=tests&style=flat-square)](https://github.com/spiral-packages/signed-urls/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spiral-packages/signed-urls.svg?style=flat-square)](https://packagist.org/packages/spiral-packages/signed-urls)

The package allows you to easily create "signed" URLs to named routes. These URLs have a "signature" hash appended to
the query string which allows Spiral Framework to verify that the URL has not been modified since it was created.

Signed URLs are especially useful for routes that are publicly accessible yet need a layer of protection against URL
manipulation.

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP 8.1+
- Spiral framework 3.0+

## Installation

You can install the package via composer:

```bash
composer require spiral-packages/signed-urls
```

After package install you need to register bootloader from the package.

```php
protected const LOAD = [
    // ...
    \Spiral\SignedUrls\Bootloader\SignedUrlsBootloader::class,
];
```

> **Note**
> if you are using [`spiral-packages/discoverer`](https://github.com/spiral-packages/discoverer),
> you don't need to register bootloader by yourself.

Specify env variables

```dotenv
# Secret key for generating the HMAC variant of the message digest.
# REQUIRED
SIGNED_URLS_KEY=secret

# Name of selected hashing algorithm (i.e. "md5", "sha256", "haval160,4", etc..)
# OPTIONAL (sha256 by default)
SIGNED_URLS_ALGO=sha256
```

## Usage

For example, you might use signed URLs to implement a public "email verification" link that is emailed to your
customers:

```php
class VerifyEmailNotification 
{
    public function __construct(
        private readonly \Spiral\SignedUrls\UrlGeneratorInterface $urls
        private readonly  \Spiral\Views\ViewInterface $view
    ) {}
    
    public function buildView(): string
    {
        return $this->view->render([
            'signed_url' => $this->urls->signedRoute(
                route: 'verify-email',
                parameters: ['user_id' => 100]
            )
        ]);
    }
}
```

If you would like to generate a temporary signed route URL that expires after a specified amount of time, you may pass
expiration date in method. When Spiral Framework validates a temporary signed route URL, it will ensure that the
expiration timestamp that is encoded into the signed URL has not expired:

```php
class VerifyEmailNotification 
{
    public function __construct(
        private readonly \Spiral\SignedUrls\UrlGeneratorInterface $urls
        private readonly  \Spiral\Views\ViewInterface $view
    ) {}
    
    public function buildView(): string
    {
        return $this->view->render([
            'signed_url' => $this->urls->signedRoute(
                route: 'verify-email',
                parameters: ['user_id' => 100],
                expiration: new \DateTime('...')
            )
        ]);
    }
}
```

You may sign not only routes but also Urls:

```php
class VerifyEmailNotification 
{
    public function __construct(
        private readonly \Spiral\SignedUrls\UrlGeneratorInterface $urls
        private readonly  \Spiral\Views\ViewInterface $view
    ) {}
    
    public function buildView(): string
    {
        return $this->view->render([
            'signed_url' => $this->urls->signedUrl(
                uri: new \Nyholm\Psr7\Uri('http://site.com/verify-email/?user_id=1'),
                expiration: new \DateTime('...')
            )
        ]);
    }
}
```

### Validating Signed Urls

To verify that a URL has a valid signature, you should call the hasValidSignature method:

```php
class EmailVerificationController
{
    public function __construct(
        private readonly \Spiral\SignedUrls\UrlGeneratorInterface $urls
    ) {}
    
    
    public function verify(\Psr\Http\Message\RequestInterface $request): string
    {
        if (!$this->urls->hasValidSignature($request->getUri())) {
            return 'ERROR';
        }
        
        return 'OK';
    }
}
```

Instead of validating signed URLs using the incoming request instance, you may assign the
`Spiral\SignedUrls\Middleware\ValidateSignature` middleware to the route:

```php
class EmailVerificationController
{
    public function __construct(
        private readonly \Spiral\SignedUrls\UrlGeneratorInterface $urls
    ) {}
    
    #[\Spiral\Router\Annotation\Route(
        name: 'verify-email',
        route: '...',
        middleware: \Spiral\SignedUrls\Middleware\ValidateSignature::class
    )]
    public function verify(\Psr\Http\Message\RequestInterface $request): string
    {
        return 'OK';
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Pavel Buchnev](https://github.com/butschster)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
