<?php

namespace Knik\Binn\Decoder;

use Knik\Binn\Binn;

class Unpacker
{
    public static function unpackType8(string $bytes): int
    {
        return unpack("C", $bytes)[1];
    }

    public static function unpackType16(string $bytes): int
    {
        return unpack("n", $bytes)[1];
    }

    public static function unpackUint64($bytes): int
    {
        return unpack("J", $bytes)[1];
    }

    public static function unpackUint32($bytes): int
    {
        if (strlen($bytes) !== 4) {
            $a = 1;
        }
        return unpack("N", $bytes)[1];
    }

    public static function unpackUint16($bytes): int
    {
        return unpack("n", $bytes)[1];
    }

    public static function unpackUint8($bytes): int
    {
        return unpack("C", $bytes)[1];
    }

    public static function unpackInt8($bytes): int
    {
        return unpack("c", $bytes)[1];
    }

    public static function unpackInt16($bytes): int
    {
        return unpack("s", strrev($bytes))[1];
    }

    public static function unpackInt32($bytes): int
    {
        return unpack("i", strrev($bytes))[1];
    }

    public static function unpackInt64($bytes): int
    {
        return unpack("q", strrev($bytes))[1];
    }

    public static function unpackFloat32($bytes): float
    {
        return unpack("G", strrev($bytes))[1];
    }

    public static function unpackFloat64($bytes): float
    {
        return unpack("E", strrev($bytes))[1];
    }

    public static function unpackString($bytes): string
    {
        return unpack("a*", $bytes)[1];
    }

    public static function unpackSize8($bytes): int
    {
        return self::unpackUint8($bytes);
    }

    public static function unpackSize32($bytes): int
    {
        $size = self::unpackUint32($bytes);
        return ($size ^ 0x80000000);
    }
}
