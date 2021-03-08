<?php

namespace Knik\Binn\Encoder\Containers;

use Knik\Binn\Binn;
use Knik\Binn\Contracts\BinnValueEncoder;
use Knik\Binn\Encoder\EncoderCollection;
use Knik\Binn\Encoder\Packer;
use Knik\Binn\Exceptions\InvalidArrayException;

class BinnListEncoder implements BinnValueEncoder
{
    public const TYPE = Binn::BINN_LIST;

    /** @var EncoderCollection */
    private $encoders;

    public function __construct(EncoderCollection $encoders)
    {
        $this->encoders = $encoders;
    }

    public function encode($value): string
    {
        if ($this->isArrayAssoc($value)) {
            throw new InvalidArrayException('Array should be sequential');
        }

        $encodedItems = '';

        foreach ($value as $item) {
            /** @var BinnValueEncoder $encoder */
            foreach ($this->encoders->getAll() as $encoder) {
                if ($encoder->supportsEncoding($item)) {
                    $encodedItems .= $encoder->encode($item);
                    break;
                }
            }
        }

        $encodedType  = Packer::packUint8(self::TYPE);
        $encodedCount = Packer::packSize(count($value));
        $encodedSize  = Packer::packSize(
            strlen($encodedType) + strlen($encodedCount) + strlen($encodedItems),
            true
        );

        return $encodedType
            . $encodedSize
            . $encodedCount
            . $encodedItems;
    }

    public function supportsEncoding($value): bool
    {
        return is_array($value) && !$this->isArrayAssoc($value);
    }

    private function isArrayAssoc($arr)
    {
        $arr = (array)$arr;

        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
