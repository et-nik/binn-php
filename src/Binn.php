<?php
/**
 * Binn. Serialize to bin string.
 * Binn Specification: https://github.com/liteserver/binn/blob/master/spec.md
 *
 * Note! This class not support Map and Object, only List support. Sorry, i am working on this.
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

class Binn extends BinnAbstract {

    /**
     * Size bin string in bytes
     *
     * @var int
     * @access protected
     */
    protected $size         = 0;

    /**
     * Bin string
     *
     * @var string
     * @access protected
     */
    protected $binnString     = "";

    /**
     * Object elements
     *
     * @var array
     * @access protected
     */
    protected $binnArr = [];

    public function binnFree()
    {
        // $this->binnType     = self::BINN_STORAGE_NOBYTES;

        $this->count        = 0;
        $this->dataSize     = 0;

        // Initial meta size 3 bytes
        // Type byte + Size byte + Item counts byte
        $this->metaSize    = self::MIN_BINN_SIZE;

        $this->size         = 0;
        $this->binnString   = "";

        $this->binnArr      = [];

        return $this;
    }

    /**
     *
     *  @return array
     */
    public function getBinnArr()
    {
        $return = [];

        foreach ($this->binnArr as &$arr) {
            switch ($arr[self::KEY_TYPE]) {
                case self::BINN_LIST:
                case self::BINN_MAP:
                case self::BINN_OBJECT:
                    $return[] = $arr[self::KEY_VAL]->getBinnArr();
                    break;

                case self::BINN_BOOL:
                case self::BINN_TRUE:
                case self::BINN_FALSE:
                case self::BINN_INT64:
                case self::BINN_UINT64:
                case self::BINN_INT32:
                case self::BINN_UINT32:
                case self::BINN_INT16:
                case self::BINN_UINT16:
                case self::BINN_INT8:
                case self::BINN_UINT8:
                case self::BINN_STRING:
                case self::BINN_FLOAT:
                case self::BINN_FLOAT32:
                case self::BINN_FLOAT64:
                    $return[] = $arr[self::KEY_VAL];
                    break;
            }
        }

        return $return;
    }

    /**
     * @return int
     */
    public function binnSize()
    {
        return $this->calculateSize();
    }

    /**
     * @param null $value
     * @return int
     */
    protected function detectType($value = null)
    {
        if (is_bool($value)) {
            return $value ? self::BINN_TRUE : self::BINN_FALSE;
        }

        if (is_string($value)) {
            return self::BINN_STRING;
        }

        if (is_integer($value)) {
            return $this->detectInt($value);
        }

        if (is_float($value)) {
            return self::BINN_FLOAT64;
        }

        if (is_object($value)) {
            return self::BINN_OBJECT;
        }

        if (is_array($value)) {
            if (!$this->isAssoc($value)) {
                return self::BINN_LIST;
            }

            // TODO: detect map and object
            if (count(array_filter(array_keys($value), 'is_string')) > 0) {
                return self::BINN_OBJECT;
            } else {
                return self::BINN_MAP;
            }
        }

        return self::BINN_NULL;
    }

    /**
     * Detect integer type
     *
     * @param $value
     * @return int
     */
    protected function detectInt($value)
    {
        if ($value < 0) {
            // int
            if ($value >= self::INT8_MIN) {
                return self::BINN_INT8;
            } else if ($value >= self::INT16_MIN) {
                return self::BINN_INT16;
            } else if ($value >= self::BINN_INT32) {
                return self::BINN_INT32;
            } else {
                return self::BINN_INT64;
            }
        } else {
            // uint
            if ($value <= self::UINT8_MAX) {
                return self::BINN_UINT8;
            } else if ($value <= self::UINT16_MAX) {
                return self::BINN_UINT16;
            } else if ($value <= self::UINT32_MAX) {
                return self::BINN_UINT32;
            } else {
                return self::BINN_UINT64;
            }
        }
    }

    /**
     * @param array $arr
     * @return bool
     */
    protected function isAssoc($arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     *
     * @param int $int_val
     *
     * @return string   HEX string
     */
    protected function getInt32Binsize($int_val = 0)
    {
        $int_val = ($int_val | (1 << 31)); // Add byte
        return pack("N", $int_val);
    }

    /**
     *
     *  @return int
     */
    protected function calculateSize()
    {
        $size = 0;

        if (($this->dataSize + $this->metaSize) > 127) {
            $size += 3;
        }

        if (count($this->binnArr) > 127) {
            $size += 3;
        }

        $this->size = ($this->dataSize + $this->metaSize) + $size;
        return $this->size;
    }

    /**
     * @param int   $type
     * @param mixed   $val
     *
     * @return int  $type2
     *
     */
    protected function compressInt($type, $val)
    {
        $type2 = $type;

        if ($val >= 0) {
            // Convert to unsigned
            switch ($type) {
                case self::BINN_INT64:
                    $type = self::BINN_UINT64;
                    break;

                case self::BINN_INT32:
                    $type = self::BINN_UINT32;
                    break;

                case self::BINN_INT16:
                    $type = self::BINN_UINT16;
                    break;
            }
        }

        if (in_array($type, [self::BINN_INT64, self::BINN_INT32, self::BINN_INT16])) {
            // Signed
            if ($val >= self::INT8_MIN) {
                $type2 = self::BINN_INT8;
            }
            elseif ($val >= self::INT16_MIN) {
                $type2 = self::BINN_INT16;
            }
            elseif ($val >= self::INT32_MIN) {
                $type2 = self::BINN_INT32;
            }
        }

        if (in_array($type, [self::BINN_UINT64, self::BINN_UINT32, self::BINN_UINT16])) {
            // Unsigned

            if ($val <= self::UINT8_MAX) {
                $type2 = self::BINN_UINT8;
            }
            elseif ($val <= self::UINT16_MAX) {
                $type2 = self::BINN_UINT16;
            }
            elseif ($val <= self::UINT32_MAX) {
                $type2 = self::BINN_UINT32;
            }
        }

        return $type2;
    }
}