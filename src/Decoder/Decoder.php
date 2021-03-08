<?php

namespace Knik\Binn\Decoder;

use Knik\Binn\Binn;

abstract class Decoder
{
    protected function detectType(string $bytes): int
    {
        if ($bytes === '') {
            return Binn::BINN_NULL;
        }

        $type = Unpacker::unpackType8($bytes[0]);

        if ($type & Binn::BINN_STORAGE_HAS_MORE) {
            $typeBytes = substr($bytes, 0, 2);
            $type = Unpacker::unpackType16($typeBytes);
        }

        return $type;
    }

    protected function readSizeWithType(string $bytes): int
    {
        $type = ($this->detectType($bytes) & ~ Binn::BINN_TYPE_MASK);

        switch ($type) {
            case Binn::BINN_STORAGE_NOBYTES:
                return 1;
            case Binn::BINN_STORAGE_BYTE:
                return 2;
            case Binn::BINN_STORAGE_WORD:
                return 3;
            case Binn::BINN_STORAGE_DWORD:
                return 5;
            case Binn::BINN_STORAGE_QWORD:
                return 9;
            case Binn::BINN_STORAGE_STRING:
                return $this->readSizeStringWithType($bytes);
            case Binn::BINN_STORAGE_BLOB:
                return $this->readSizeBlobWithType($bytes);
            case Binn::BINN_STORAGE_CONTAINER:
                return $this->readSizeContainerWithType($bytes);
        }

        return 0;
    }

    private function readSizeStringWithType(string $bytes): int
    {
        // type, size size, string size, null terminator
        return $this->readSizeBlobWithType($bytes) + 1;
    }

    private function readSizeBlobWithType(string $bytes): int
    {
        $size = Unpacker::unpackSize8($bytes[1]);
        $sizeSize = 1;

        if ($size > Binn::BINN_MAX_ONE_BYTE_SIZE) {
            $sizeBytes = substr($bytes, 1, 4);
            $size = Unpacker::unpackSize32($sizeBytes);
            $sizeSize = 4;
        }

        // type, size size, data size
        return $size + $sizeSize + 1;
    }

    private function readSizeContainerWithType(string $bytes): int
    {
        $size = Unpacker::unpackSize8($bytes[1]);

        if ($size > Binn::BINN_MAX_ONE_BYTE_SIZE) {
            $sizeBytes = substr($bytes, 1, 4);
            $size = Unpacker::unpackSize32($sizeBytes);
        }

        return $size;
    }
}
