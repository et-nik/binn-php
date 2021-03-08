<?php

namespace Knik\Binn\Tests\Unit\Encoder;

use Knik\Binn\Tests\BinnTestCase;
use PHPUnit\Framework\Assert;

class BlobEncodeTest extends BinnTestCase
{
    public function testBlobEncode()
    {
        $encoder = $this->getEncoder();
        $file = fopen('tests/Files/file.jpg', 'rb');

        $result = $encoder->encode($file, 'binn');

        Assert::assertEquals("\xC0\x80\x00\x61\x24", substr($result, 0, 5));
        Assert::assertStringEqualsFile('tests/Files/file.jpg', substr($result, 5));
    }

}
