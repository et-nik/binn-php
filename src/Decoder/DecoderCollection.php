<?php

namespace Knik\Binn\Decoder;

use Knik\Binn\Contracts\BinnValueDecoder;

class DecoderCollection
{
    /** @var BinnValueDecoder[] */
    private $decoders = [];

    /** @var array */
    private $mapper = [];

    public function getAll(): array
    {
        return $this->decoders;
    }

    public function findByType(int $type): ?BinnValueDecoder
    {
        if (array_key_exists($type, $this->mapper)) {
            return $this->mapper[$type];
        }

        return null;
    }

    public function add(int $type, BinnValueDecoder $decoder): void
    {
        if (!in_array($decoder, $this->decoders, true)) {
            $this->decoders[] = $decoder;
        }

        $this->mapper[$type] = $decoder;
    }
}
