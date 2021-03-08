<?php

namespace Knik\Binn\Tests\Unit\Encoder;

use Knik\Binn\Tests\BinnTestCase;
use PHPUnit\Framework\Assert;

class BinnEncodeTest extends BinnTestCase
{
    /** @dataProvider valuesDataProvider  */
    public function testEncode($expectedBytes, $value): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode($value, 'binn');

        Assert::assertEquals($expectedBytes, $result);
    }

    public function testSimpleTypeEncode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode(7, 'binn');

        Assert::assertEquals("\x20\x07", $result);
    }

    public function testInt16Encode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode(513, 'binn');

        Assert::assertEquals("\x40\x02\x01", $result);
    }

    public function testInt32Encode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode(100000, 'binn');

        Assert::assertEquals("\x60\x00\x01\x86\xA0", $result);
    }

    public function testInt64Encode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode(9223372036854775807, 'binn');

        Assert::assertEquals("\x80\x7F\xFF\xFF\xFF\xFF\xFF\xFF\xFF", $result);
    }

    public function testBigListEncode(): void
    {
        $encoder = $this->getEncoder();

        $result = $encoder->encode([str_repeat('string', 200)], 'binn');

        Assert::assertEquals("\xE0\x80\x00\x04\xBC\x01\xA0\x80\x00\x04\xB0"
                . str_repeat('string', 200)
                . "\x00",
            $result
        );
    }
}
