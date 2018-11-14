<?php

namespace Knik\Binn;

abstract class BinnAbstract
{
    // Consts from original C++ Library
    const BINN_LIST         = 0xE0;
    const BINN_MAP          = 0xE1;
    const BINN_OBJECT       = 0xE2;

    const BINN_UINT8        = 0x20;
    const BINN_INT8         = 0x21;
    const BINN_UINT16       = 0x40;
    const BINN_INT16        = 0x41;
    const BINN_UINT32       = 0x60;
    const BINN_INT32        = 0x61;
    const BINN_UINT64       = 0x80;
    const BINN_INT64        = 0x81;
    const BINN_STRING       = 0xA0;

    const BINN_FLOAT32      = 0x62;  // (DWORD)
    const BINN_FLOAT64      = 0x82;  // (QWORD)
    const BINN_FLOAT        = self::BINN_FLOAT32;

    const BINN_BOOL         = 0x80061;

    const BINN_STORAGE_NOBYTES      = 0x00;
    const BINN_STORAGE_BYTE         = 0x20;  //  8 bits
    const BINN_STORAGE_WORD         = 0x40;  // 16 bits -- the endianess (byte order) is automatically corrected
    const BINN_STORAGE_DWORD        = 0x60;  // 32 bits -- the endianess (byte order) is automatically corrected
    const BINN_STORAGE_QWORD        = 0x80;  // 64 bits -- the endianess (byte order) is automatically corrected
    const BINN_STORAGE_STRING       = 0xA0;  // Are stored with null termination
    const BINN_STORAGE_BLOB         = 0xC0;
    const BINN_STORAGE_CONTAINER    = 0xE0;

    const BINN_NULL                 = 0x00;
    const BINN_TRUE                 = 0x01;
    const BINN_FALSE                = 0x02;

    const UINT8_MAX                 = 255;
    const UINT16_MAX                = 65535;
    const UINT32_MAX                = 4294967295;
    const UINT64_MAX                = 18446744073709551615;

    const INT8_MIN                  = -128;
    const INT8_MAX                  = 127;
    const INT16_MIN                 = -32768;
    const INT16_MAX                 = 32767;
    const INT32_MIN                 = -2147483648;
    const INT32_MAX                 = 2147483647;
    const INT64_MIN                 = -9223372036854775808;
    const INT64_MAX                 = 9223372036854775807;

    const BINN_STORAGE_MASK         = 0xE0;
    const BINN_TYPE_MASK            = 0x0F;

    const MIN_BINN_SIZE             = 3;

    // PHP Library consts
    const KEY_TYPE                 = 0;
    const KEY_VAL                  = 1;
    const KEY_SIZE                 = 2;
    const KEY_KEY                  = 3;

    /**
     * Binn object type: self::BINN_LIST, self::BINN_MAP, self::BINN_OBJECT
     *
     * @var int $binnType
     * @access protected
     */
    protected $binnType = self::BINN_NULL;

    /**
     * @var string
     */
    protected $binnClass = null;

    /**
     * Count elements in object
     *
     * @var int
     * @access protected
     */
    protected $count        = 0;

    /**
     * Data size in bytes
     *
     * @var int
     * @access protected
     */
    protected $dataSize    = 0;

    /**
     * Meta size in bytes
     *
     * @var int
     */
    protected $metaSize    = self::MIN_BINN_SIZE;

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

    /**
     * @var array
     *
     * Associations container int with container classes
     *
     * Example values:
     * [
     *  0xE0 => \Knik\Binn\BinnList::class,
     *  0xE1 => \Knik\Binn\BinnMap::class,
     *  0xE2 => \Knik\Binn\BinnObject::class,
     * ]
     */
    protected $containersClasses = [
        0xE0 => \Knik\Binn\BinnList::class,
        0xE1 => \Knik\Binn\BinnMap::class,
        0xE2 => \Knik\Binn\BinnObject::class,
    ];

    /**
     * @param $containersClasses
     */
    public function setContainersClasses($containersClasses)
    {
        $this->containersClasses = $containersClasses;
    }

    /**
     *
     * @param int $intVal
     *
     * @return string   HEX string
     */
    protected function getInt32Binsize($intVal = 0)
    {
        $intVal = ($intVal | (1 << 31)); // Add byte
        return $this->pack(self::BINN_UINT32, $intVal);
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
            if (strlen($value) > 4) {
                return self::BINN_FLOAT64;
            } else {
                return self::BINN_FLOAT32;
            }
        }

        if (is_object($value)) {
            return self::BINN_OBJECT;
        }

        if (is_array($value)) {
            if (!$this->isArrayAssoc($value)) {
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

    protected function storageType($type)
    {
        return $type & ($type ^ self::BINN_TYPE_MASK);
    }

    /**
     * @param array $arr
     * @return bool
     */
    protected static function isArrayAssoc($arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * @param $arr
     * @return bool
     */
    protected static function isArrayObject($arr)
    {
        foreach(array_keys($arr) as $key) {
            if (!is_int($key)) {
                return true;
            }
        }

        return false;
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
     *
     *  @return array
     */
    public function getBinnArr()
    {
        $return = [];

        foreach ($this->binnArr as $arr) {
            $storageType = $this->storageType($arr[self::KEY_TYPE]);

            if ($storageType === self::BINN_STORAGE_CONTAINER) {
                if (isset($arr[self::KEY_KEY])) {
                    $key = $arr[self::KEY_KEY];
                    $return[$key] = $arr[self::KEY_VAL]->getBinnArr();
                } else {
                    $return[] = $arr[self::KEY_VAL]->getBinnArr();
                }
            } else {
                if (isset($arr[self::KEY_KEY])) {
                    $key = $arr[self::KEY_KEY];
                    $return[$key] = $arr[self::KEY_VAL];
                } else {
                    $return[] = $arr[self::KEY_VAL];
                }
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
     */
    protected function unpack($varType, $value)
    {
        if (($varType & self::BINN_STORAGE_CONTAINER) == self::BINN_STORAGE_CONTAINER) {

        }
            
        if ($varType == self::BINN_TRUE) {
            return true;
        } else if ($varType == self::BINN_FALSE) {
            return false;
        } else if ($varType == self::BINN_UINT64) {
            return unpack("J", $value)[1];
        } else if ($varType == self::BINN_UINT32) {
            return unpack("N", $value)[1];
        } else if ($varType == self::BINN_UINT16) {
            return unpack("n", $value)[1];
        } else if ($varType == self::BINN_UINT8) {
            return unpack("C", $value)[1];
        } else if ($varType == self::BINN_INT8) {
            return unpack("c", $value)[1];
        } else if ($varType == self::BINN_INT16) {
            return unpack("s", strrev($value))[1];
        } else if ($varType == self::BINN_INT32) {
            return unpack("i", strrev($value))[1];
        } else if ($varType == self::BINN_INT64) {
            return unpack("q", strrev($value))[1];
        } else if ($varType == self::BINN_FLOAT32) {
            return unpack("G", strrev($value))[1];
        } else if ($varType == self::BINN_FLOAT64) {
            return unpack("E", strrev($value))[1];
        } else if ($varType == self::BINN_STRING) {
            return unpack("a*", $value)[1];
        }
        
        return null;
    }

    /**
     * 
     */
    protected function pack($varType, $value = null)
    {
        if ($this->storageType($varType) === self::BINN_STORAGE_CONTAINER) {

        }

        if ($varType == self::BINN_TRUE) {
            return pack("C", self::BINN_TRUE);
        } else if ($varType == self::BINN_FALSE) {
            return pack("C", self::BINN_FALSE);
        } else if ($varType == self::BINN_UINT64) {
            return pack("J", $value);
        } else if ($varType == self::BINN_UINT32) {
            return pack("N", $value);
        } else if ($varType == self::BINN_UINT16) {
            return pack("n", $value);
        } else if ($varType == self::BINN_UINT8) {
            return pack("C", $value);
        } else if ($varType == self::BINN_INT8) {
            return pack("c", $value);
        } else if ($varType == self::BINN_INT16) {
            return strrev(pack("s", $value));
        } else if ($varType == self::BINN_INT32) {
            return strrev(pack("i", $value));
        } else if ($varType == self::BINN_INT64) {
            return strrev(pack("q", $value));
        } else if ($varType == self::BINN_FLOAT32) {
            return strrev(pack("G", $value));
        } else if ($varType == self::BINN_FLOAT64) {
            return strrev(pack("E", $value));
        } else if ($varType == self::BINN_STRING) {
            return pack("a*", $value);
        } else if ($varType == self::BINN_NULL) {
            return pack("x");
        }
        
        return null;
    }

    /**
     * @param $type
     * @return string
     */
    protected function packType($type)
    {
        return $this->pack(self::BINN_UINT8, $type);
    }

    /**
     * @param $type
     * @return string
     */
    protected function packSize($size)
    {
        return ($size <= 127)
            ? $this->pack(self::BINN_UINT8, $size)
            : $this->getInt32Binsize($size);
    }
    
    protected function getTypeSize($type, $value = '')
    {
        $size = ['meta' => 0, 'data' => 0];
        $storageType = $this->storageType($type);

        if ($type == self::BINN_BOOL) {
            $size = ['meta' => 1, 'data' => 0];
        } else if ($storageType === self::BINN_STORAGE_CONTAINER) {
            $size = ['meta' => 0, 'data' => $value->binnSize()];
        } else if ($storageType === self::BINN_STORAGE_BLOB) {
            $dataSize = mb_strlen($value);

            $metaSize = $dataSize > 127 ? 4 : 1; // size byte
            $metaSize += 1; // type byte

            $size = ['meta' => $metaSize, 'data' => $dataSize];
        } else if ($storageType === self::BINN_STORAGE_STRING) {
            $dataSize = mb_strlen($value);

            $metaSize = $dataSize > 127 ? 4 : 1; // size byte
            $metaSize += 2; // type byte + null terminated
            
            $size = ['meta' => $metaSize, 'data' => $dataSize];
        } else if ($storageType === self::BINN_STORAGE_QWORD) {
            $size = ['meta' => 1, 'data' => 8];
        } else if ($storageType === self::BINN_STORAGE_DWORD) {
            $size = ['meta' => 1, 'data' => 4];
        } else if ($storageType === self::BINN_STORAGE_WORD) {
            $size = ['meta' => 1, 'data' => 2];
        } else if ($storageType === self::BINN_STORAGE_BYTE) {
            $size = ['meta' => 1, 'data' => 1];
        } else if ($storageType === self::BINN_STORAGE_NOBYTES) {
            $size = ['meta' => 1, 'data' => 0];
        }
        
        return $size;
    }
}