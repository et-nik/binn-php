<?php

namespace Knik\Binn\Decoder;

use Knik\Binn\Binn;
use Knik\Binn\Contracts\BinnValueDecoder;

class SimpleStorageValueDecoder extends Decoder implements BinnValueDecoder
{
    public function decode(string $bytes)
    {
        $type = $this->detectType($bytes);

        switch ($type) {
            case Binn::BINN_NULL:
                return null;

            case Binn::BINN_TRUE:
                return true;

            case Binn::BINN_FALSE:
                return false;

            case Binn::BINN_FLOAT32:
                return Unpacker::unpackFloat32(
                    substr($bytes, 1, 4)
                );

            case Binn::BINN_FLOAT64:
                return Unpacker::unpackFloat64(
                    substr($bytes, 1, 8)
                );

            case Binn::BINN_INT64:
                return Unpacker::unpackInt64(
                    substr($bytes, 1, 8)
                );

            case Binn::BINN_INT32:
                return Unpacker::unpackInt32(
                    substr($bytes, 1, 4)
                );

            case Binn::BINN_INT16:
                return Unpacker::unpackInt16(
                    substr($bytes, 1, 2)
                );

            case Binn::BINN_INT8:
                return Unpacker::unpackInt8(
                    $bytes[1]
                );

            case Binn::BINN_UINT64:
                return Unpacker::unpackUint64(
                    substr($bytes, 1, 8)
                );

            case Binn::BINN_UINT32:
                return Unpacker::unpackUint32(
                    substr($bytes, 1, 4)
                );

            case Binn::BINN_UINT16:
                return Unpacker::unpackUint16(
                    substr($bytes, 1, 2)
                );

            case Binn::BINN_UINT8:
                return Unpacker::unpackUint8($bytes[1]);

            case Binn::BINN_STRING:
                return $this->decodeString($bytes);

            case Binn::BINN_STORAGE_BLOB:
                return $this->decodeString($bytes);
        }

        return null;
    }

    public function supportsDecoding(string $bytes): bool
    {
        $type = $this->detectType($bytes);

        return in_array($type, [
            Binn::BINN_NULL,
            Binn::BINN_TRUE,
            Binn::BINN_FALSE,
            Binn::BINN_FLOAT32,
            Binn::BINN_FLOAT64,
            Binn::BINN_INT64,
            Binn::BINN_INT32,
            Binn::BINN_INT16,
            Binn::BINN_INT8,
            Binn::BINN_UINT64,
            Binn::BINN_UINT32,
            Binn::BINN_UINT16,
            Binn::BINN_UINT8,
            Binn::BINN_STRING,
            Binn::BINN_STORAGE_BLOB,
        ], true);
    }

    private function decodeString(string $bytes): string
    {
        $size = Unpacker::unpackSize8($bytes[1]);
        $offset = 2;

        if ($size > Binn::BINN_MAX_ONE_BYTE_SIZE) {
            $sizeBytes = substr($bytes, 1, 4);
            $size = Unpacker::unpackSize32($sizeBytes);
            $offset = 5;
        }

        $stringBytes = substr($bytes, $offset, $size);

        return Unpacker::unpackString($stringBytes);
    }
}
