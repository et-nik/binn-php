<?php

namespace Knik\Binn\Encoder\Containers;

use Knik\Binn\Binn;
use Knik\Binn\Contracts\BinnValueEncoder;
use Knik\Binn\Encoder\EncoderCollection;
use Knik\Binn\Encoder\Packer;

class BinnObjectEncoder implements BinnValueEncoder
{
    public const TYPE = Binn::BINN_OBJECT;

    /** @var EncoderCollection */
    private $encoders;

    public function __construct(EncoderCollection $encoders)
    {
        $this->encoders = $encoders;
    }

    public function encode($value): string
    {
        $encodedData = '';

        $count = 0;
        foreach ($value as $key => $item) {
            $count++;
            /** @var BinnValueEncoder $encoder */
            foreach ($this->encoders->getAll() as $encoder) {
                if ($encoder->supportsEncoding($item)) {
                    $encodedData .= Packer::packUint8(strlen($key));
                    $encodedData .= Packer::packString($key);
                    $encodedData .= $encoder->encode($item);
                    break;
                }
            }
        }

        $encodedType  = Packer::packUint8(self::TYPE);
        $encodedCount = Packer::packSize($count);
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
        return is_array($value) || is_object($value);
    }
}
