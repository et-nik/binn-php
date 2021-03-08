<?php

namespace Knik\Binn\Tests\Unit\Encoder;

use Knik\Binn\Encoder\BinnEncoder;
use Knik\Binn\Tests\BinnTestCase;
use PHPUnit\Framework\Assert;

class BinnEncoderTest extends BinnTestCase
{
    public function testEncode()
    {
        $encoder = new BinnEncoder();

        $result = $encoder->encode(['test' => 'test'], 'binn');

        Assert::assertEquals("\xE2\x0F\x01\x04\x74\x65\x73\x74\xA0\x04\x74\x65\x73\x74\x00", $result);
    }

    public function testDecode()
    {
        $encoder = new BinnEncoder();

        $result = $encoder->decode("\xE2\x0F\x01\x04\x74\x65\x73\x74\xA0\x04\x74\x65\x73\x74\x00", 'binn');

        Assert::assertEquals(['test' => 'test'], $result);
    }
}
