<?php

namespace Knik\Binn\Encoder;

use Knik\Binn\Contracts\BinnValueEncoder;

class BinnEncode
{
    /** @var EncoderCollection */
    private $encoders;

    public function __construct(EncoderCollection $encoders)
    {
        $this->encoders = $encoders;
    }

    public function encode($value, $format, $context = []): ?string
    {
        /** @var BinnValueEncoder $encoder */
        foreach ($this->encoders->getAll() as $encoder) {
            if ($encoder->supportsEncoding($value)) {
                return $encoder->encode($value);
            }
        }

        return "\x00";
    }
}
