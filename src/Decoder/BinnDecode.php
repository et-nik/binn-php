<?php

namespace Knik\Binn\Decoder;

use Knik\Binn\Contracts\BinnValueDecoder;

class BinnDecode
{
    /** @var DecoderCollection */
    private $decoders;

    public function __construct(DecoderCollection $decoders)
    {
        $this->decoders = $decoders;
    }

    public function decode($value, $format, $context = [])
    {
        /** @var BinnValueDecoder $decoder */
        foreach ($this->decoders->getAll() as $decoder) {
            if ($decoder->supportsDecoding($value)) {
                return $decoder->decode($value);
            }
        }

        return null;
    }
}
