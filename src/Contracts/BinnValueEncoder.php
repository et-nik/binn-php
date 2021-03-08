<?php

namespace Knik\Binn\Contracts;

interface BinnValueEncoder
{
    public function encode($value): string;

    public function supportsEncoding($value): bool;
}
