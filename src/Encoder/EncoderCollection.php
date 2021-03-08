<?php

namespace Knik\Binn\Encoder;

use Knik\Binn\Contracts\BinnValueEncoder;

class EncoderCollection
{
    /** @var BinnValueEncoder[] */
    private $encoders = [];

    /** @var array */
    private $mapper = [];

    public function getAll(): array
    {
        return $this->encoders;
    }

    public function findByType(int $type): ?BinnValueEncoder
    {
        if (array_key_exists($type, $this->mapper)) {
            return $this->mapper[$type];
        }

        return null;
    }

    public function add(int $type, BinnValueEncoder $encoder): void
    {
        if (!in_array($encoder, $this->encoders, true)) {
            $this->encoders[] = $encoder;
        }

        $this->mapper[$type] = $encoder;
    }
}
