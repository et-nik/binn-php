<?php

namespace Knik\Binn\Tests;

use Knik\Binn\Decoder\BinnDecode;
use Knik\Binn\Decoder\DecoderCollectionFactory;
use Knik\Binn\Encoder\BinnEncode;
use Knik\Binn\Encoder\EncoderCollectionFactory;
use PHPUnit\Framework\TestCase;

class BinnTestCase extends TestCase
{
    public function valuesDataProvider()
    {
        yield ["\x00",                                 null];
        yield ["\x01",                                 true];
        yield ["\x02",                                 false];
        yield ["\x20\x06",                             6];
        yield ["\x21\xD0",                             -48];
        yield ["\x40\xD0\x06",                         53254];
        yield ["\x41\xD0\x06",                         -12282];
        yield ["\x60\xFA\xAA\xAA\xAA",                 4205488810];
        yield ["\x61\xFA\xAA\xAA\xAA",                 -89478486];
        yield ["\x82\xA9\x6A\x82\xA8\xFB\xB0\x28\x40", 12.34567];
        yield ["\xA0\x05\x68\x65\x6C\x6C\x6F\x00",     "hello"];

        yield [
            "\xA0\x80\x00\x00\x83hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello\x00",
            'hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello_hello'
        ];

        yield [
            "\xA0\x04\xF0\x9F\x98\x83\x00",
            "😃"
        ];
    }

    public function floatValuesDataProvider()
    {
        yield ["\x62\xdd\x87\x45\x41",                 12.34567];
        yield ["\x82\xa9\x6a\x82\xa8\xfb\xb0\x28\x40", 12.34567];
    }

    protected function getEncoder(): BinnEncode
    {
        $encodersFactory = new EncoderCollectionFactory();
        return new BinnEncode($encodersFactory->getCollection());
    }

    protected function getDecoder(): BinnDecode
    {
        $decoderFactory = new DecoderCollectionFactory();
        return new BinnDecode($decoderFactory->getCollection());
    }
}
