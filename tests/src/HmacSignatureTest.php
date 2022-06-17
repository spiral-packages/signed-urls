<?php

declare(strict_types=1);

namespace Spiral\SignedUrls\Tests;

use Spiral\SignedUrls\HmacSignature;
use Spiral\SignedUrls\SignatureInterface;

final class HmacSignatureTest extends \PHPUnit\Framework\TestCase
{
    private SignatureInterface $signature;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signature = new HmacSignature(
            key: 'foo',
            algo: 'sha256'
        );
    }

    public function testNotSpecifiedKeyShouldThrowAnException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectErrorMessage('You must specify a secret key for generating the HMAC variant of the message digest.');

        new HmacSignature('');
    }

    public function testGenerateSignature(): void
    {
        $signature = $this->signature->generate('bar');

        $this->assertSame('f9320baf0249169e73850cd6156ded0106e2bb6ad8cab01b7bbbebe6d1065317', $signature);
    }

    public function testCompareSignature(): void
    {
        $this->assertTrue(
            $this->signature->compare('f9320baf0249169e73850cd6156ded0106e2bb6ad8cab01b7bbbebe6d1065317', 'bar')
        );

        $this->assertFalse(
            $this->signature->compare('bar', 'bar')
        );

        $this->assertFalse(
            $this->signature->compare('test', 'bar')
        );
    }
}
