<?php

namespace Knik\Binn\Tests\Unit\Decoder;

use Knik\Binn\Tests\BinnTestCase;
use PHPUnit\Framework\Assert;

class BinnContainersDecodeTest extends BinnTestCase
{
    /** @dataProvider valuesDataProvider  */
    public function testDecode($bytes, $expected): void
    {
        $decoder = $this->getDecoder();

        $result = $decoder->decode($bytes, 'binn');

        Assert::assertEquals($expected, $result);
    }

    public function valuesDataProvider()
    {
        yield [
            "\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15",
            [123, -456, 789]
        ];

        yield [
            "\xE1\x1A\x02\x00\x00\x00\x01\xA0\x03add\x00\x00\x00\x00\x02\xE0\x09\x02\x41\xCF\xC7\x40\x1A\x85",
            [1 => 'add', 2 => [-12345, 6789]]
        ];

        yield [
            "\xE2\x11\x01\x05hello\xA0\x05world\x00",
            ['hello' => 'world']
        ];
    }
}
