<?php

namespace Knik\Binn\Encoder;

use Knik\Binn\Binn;
use Knik\Binn\Contracts\BinnValueEncoder;

class SimpleTypeValueEncoder implements BinnValueEncoder
{
    public function encode($value): string
    {
        $type = $this->detectType($value);

        return $this->encodeType($type) . $this->encodeValue($type, $value);
    }

    public function encodeValue(int $type, $value = null): ?string
    {
        if ($type === Binn::BINN_NULL) {
            return '';
        }

        if ($type === Binn::BINN_TRUE) {
            return '';
        }

        if ($type === Binn::BINN_FALSE) {
            return '';
        }

        if ($type === Binn::BINN_UINT64) {
            return Packer::packUint64($value);
        }

        if ($type === Binn::BINN_UINT32) {
            return Packer::packUint32($value);
        }

        if ($type === Binn::BINN_UINT16) {
            return Packer::packUint16($value);
        }

        if ($type === Binn::BINN_UINT8) {
            return Packer::packUint8($value);
        }

        if ($type === Binn::BINN_INT8) {
            return Packer::packInt8($value);
        }

        if ($type === Binn::BINN_INT16) {
            return Packer::packInt16($value);
        }

        if ($type === Binn::BINN_INT32) {
            return Packer::packInt32($value);
        }

        if ($type === Binn::BINN_INT64) {
            return Packer::packInt64($value);
        }

        if ($type === Binn::BINN_FLOAT32) {
            return Packer::packFloat32($value);
        }

        if ($type === Binn::BINN_FLOAT64) {
            return Packer::packFloat64($value);
        }

        if ($type === Binn::BINN_STRING) {
            return Packer::packSize(strlen($value)) . Packer::packString($value) . "\x00";
        }

        return null;
    }

    public function supportsEncoding($value): bool
    {
        return $this->detectType($value) !== null;
    }

    public function encodeType(int $type): ?string
    {
        return $this->encodeValue(Binn::BINN_UINT8, $type);
    }

    private function detectType($value): ?int
    {
        if (is_bool($value)) {
            return $value ? Binn::BINN_TRUE : Binn::BINN_FALSE;
        }

        if (is_string($value)) {
            return Binn::BINN_STRING;
        }

        if (is_int($value)) {
            return $this->detectInt($value);
        }

        if (is_float($value)) {
            if (strlen($value) > 4) {
                return Binn::BINN_FLOAT64;
            }

            return Binn::BINN_FLOAT32;
        }

        if (is_null($value)) {
            return Binn::BINN_NULL;
        }

        return null;
    }

    public function detectInt($value): int
    {
        if ($value < 0) {
            // int
            if ($value >= Binn::INT8_MIN) {
                return Binn::BINN_INT8;
            }

            if ($value >= Binn::INT16_MIN) {
                return Binn::BINN_INT16;
            }

            if ($value >= Binn::INT32_MIN) {
                return Binn::BINN_INT32;
            }

            return Binn::BINN_INT64;
        }

        // uint
        if ($value <= Binn::UINT8_MAX) {
            return Binn::BINN_UINT8;
        }

        if ($value <= Binn::UINT16_MAX) {
            return Binn::BINN_UINT16;
        }

        if ($value <= Binn::UINT32_MAX) {
            return Binn::BINN_UINT32;
        }

        return Binn::BINN_UINT64;
    }
}
