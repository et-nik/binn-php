<?php

namespace Knik\Binn\Contracts;

interface Container
{
    public function binnOpen(string $binn = '');

    public function toArray(): array;
}
