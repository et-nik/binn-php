<?php

namespace Knik\Binn\Contracts;

interface BinnValueDecoder
{
    public function decode(string $bytes);

    public function supportsDecoding(string $bytes): bool;
}
