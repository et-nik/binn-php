<?php
/**
 * Binn. Serialize to bin string.
 * Binn Specification: https://github.com/liteserver/binn/blob/master/spec.md
 *
 * Original Binn Library for C++ - https://github.com/liteserver/binn
 *
 *
 * @author      Nikita Kuznetsov (NiK)
 * @copyright   Copyright (c) 2016, Nikita Kuznetsov (nikita.hldm@gmail.com)
 * @license     GNU GPL
 * @link        http://www.gameap.ru
 *
 */

namespace Knik\Binn;

class Binn extends BinnAbstract
{
    /**
     * State. Will be removed in 1.0
     * @var array
     */
    protected $items = [];

    /**
     * State. Will be removed in 1.0
     * @var string
     */
    protected $binn = '';

    public function serialize($data = null)
    {
        if ($data === null) {
            return $this->encoder->encode($this->items, 'binn');
        }

        return $this->encoder->encode($data, 'binn');
    }

    public function unserialize($binnString = null)
    {
        if ($binnString === null) {
            return $this->decoder->decode($this->binn, 'binn');
        }

        return $this->decoder->decode($binnString, 'binn');
    }

    /**
     * @deprecated use serialize/unserialize
     */
    public function binnOpen(string $binn = ''): void
    {
        $this->binn  = $binn;
        $this->items = $this->unserialize($binn);
    }

    /**
     * @deprecated use serialize/unserialize
     */
    public function getBinnVal(): string
    {
        $this->binn = $this->serialize();
        return $this->binn;
    }

    /**
     * @deprecated
     */
    public function getBinnArr(): array
    {
        return $this->items;
    }

    /**
     * @deprecated
     */
    public function binnSize(): int
    {
        return strlen($this->binn);
    }

    /**
     * @deprecated
     */
    public function binnFree()
    {
        $this->binn = '';
        $this->items = [];

        return $this;
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
