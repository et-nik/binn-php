<?php

namespace Knik\Binn;

use Knik\Binn\Contracts\Container;

class BinnMap extends Binn implements Container
{
    protected $binnType = self::BINN_MAP;

    private function addVal($key, $value)
    {
        $this->items[$key] = $value;
    }
}
