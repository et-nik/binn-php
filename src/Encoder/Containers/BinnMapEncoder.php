<?php

namespace Knik\Binn\Encoder\Containers;

use Knik\Binn\Binn;
use Knik\Binn\Contracts\BinnValueEncoder;
use Knik\Binn\Encoder\EncoderCollection;
use Knik\Binn\Encoder\Packer;
use Knik\Binn\Exceptions\InvalidArrayException;

class BinnMapEncoder implements BinnValueEncoder
{
    public const TYPE = Binn::BINN_MAP;

    /** @var EncoderCollection */
    private $encoders;

    public function __construct(EncoderCollection $encoders)
    {
        $this->encoders = $encoders;
    }

    public function encode($value): string
    {
        if (!$this->isArrayKeyNumbers($value)) {
            throw new InvalidArrayException('Array keys should be numbers');
        }

        $encodedData = '';

        foreach ($value as $key => $item) {
            /** @var BinnValueEncoder $encoder */
            foreach ($this->encoders->getAll() as $encoder) {
                if ($encoder->supportsEncoding($item)) {
                    $encodedData .= Packer::packInt32($key);
                    $encodedData .= $encoder->encode($item);
                    break;
                }
            }
        }

        $encodedType  = Packer::packUint8(self::TYPE);
        $encodedCount = Packer::packSize(count($value));
        $encodedSize  = Packer::packSize(
            strlen($encodedType) + strlen($encodedCount) + strlen($encodedData),
            true
        );

        return $encodedType
            . $encodedSize
            . $encodedCount
            . $encodedData;
    }

    public function supportsEncoding($value): bool
    {
        return is_array($value) && $this->isArrayKeyNumbers($value);
    }

    private function isArrayKeyNumbers($arr): bool
    {
        $arr = (array)$arr;

        if ([] === $arr) {
            return false;
        }

        foreach (array_keys($arr) as $key) {
            if (!is_int($key)) {
                return false;
            }
        }

        return true;
    }
}
