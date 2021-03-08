<?php

namespace Knik\Binn\Encoder;

class Packer
{
    public static function packUint64(int $value): string
    {
        return pack("J", $value);
    }

    public static function packUint32(int $value): string
    {
        return pack("N", $value);
    }

    public static function packUint16(int $value): string
    {
        return pack("n", $value);
    }

    public static function packUint8(int $value): string
    {
        return pack("C", $value);
    }

    public static function packInt64(int $value): string
    {
        return strrev(pack("q", $value));
    }

    public static function packInt32(int $value): string
    {
        return strrev(pack("i", $value));
    }

    public static function packInt16(int $value): string
    {
        return strrev(pack("s", $value));
    }

    public static function packInt8(int $value): string
    {
        return pack("c", $value);
    }

    public static function packFloat32(float $value): string
    {
        return pack("f", $value);
    }

    public static function packFloat64(float $value): string
    {
        return pack("e", $value);
    }

    public static function packString(string $value): string
    {
        return pack("a*", $value);
    }

    public static function packSize(int $size, bool $totalSize = false): string
    {
        $sz = $size;

        if ($totalSize) {
            $sz++;
        }

        if ($sz <= 127) {
            return self::packUint8($sz);
        }

        if ($totalSize) {
            $sz += 3;
        }

        return self::packSize32($sz);
    }

    public static function packSize32($size = 0): string
    {
        $sizeWithBit = $size | (1 << 31);
        return self::packUint32($sizeWithBit);
    }

    public static function packNull(): string
    {
        return "\x00";
    }
}
