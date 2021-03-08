<?php

namespace Knik\Binn\Tests\Unit\Decoder;

use Knik\Binn\Tests\BinnTestCase;
use PHPUnit\Framework\Assert;

class BinnDecodeTest extends BinnTestCase
{
    /**
     * @dataProvider valuesDataProvider
     * @dataProvider floatValuesDataProvider
     */
    public function testDecode($bytes, $expected): void
    {
        $decoder = $this->getDecoder();

        $result = $decoder->decode($bytes, 'binn');

        Assert::assertEqualsWithDelta($expected, $result, 0.00001);
    }

    public function testDecodeLongSize(): void
    {
        $decoder = $this->getDecoder();

        $result = $decoder->decode("\xA0\x80\x00\x00\x05\x68\x65\x6C\x6C\x6F\x00", 'binn');

        Assert::assertEquals('hello', $result);
    }

    public function testBlobDecode()
    {
        $decoder = $this->getDecoder();

        $result = $decoder->decode("\xC0\x80\x00\x00\x05\xFF\xFF\xFF\xFF\xFF", 'binn');

        Assert::assertEquals("\xFF\xFF\xFF\xFF\xFF", $result);
    }
}
