<?php

namespace Knik\Binn;
use Knik\Binn\Exceptions\InvalidArrayException;

/**
 * @method BinnList addBool(boolean $value)
 * @method BinnList addUint8(integer $value)
 * @method BinnList addUint16(integer $value)
 * @method BinnList addUint32(integer $value)
 * @method BinnList addUint64(integer $value)
 * @method BinnList addInt8(integer $value)
 * @method BinnList addInt16(integer $value)
 * @method BinnList addInt32(integer $value)
 * @method BinnList addInt64(integer $value)
 * @method BinnList addStr(string $value)
 * @method BinnList addList(Binn $value)
 * @method BinnList addMap(Binn $value)
 * @method BinnList addObject(Binn $value)
 *
 */
class BinnList extends Binn
{
    /**
     * @var array
     */
    private $methodsAssignments = [
        'addBool'      => self::BINN_BOOL,
        'addUint8'     => self::BINN_UINT8,
        'addUint16'    => self::BINN_UINT16,
        'addUint32'    => self::BINN_UINT32,
        'addUint64'    => self::BINN_UINT64,
        'addInt8'      => self::BINN_INT8,
        'addInt16'     => self::BINN_INT16,
        'addInt32'     => self::BINN_INT32,
        'addInt64'     => self::BINN_INT64,
        'addStr'       => self::BINN_STRING,
        'addList'      => self::BINN_LIST,
        'addMap'       => self::BINN_MAP,
        'addObject'    => self::BINN_OBJECT,
    ];

    public function __construct($binnString = '')
    {
        $this->binnType = self::BINN_LIST;
        $this->binnClass = self::class;

        if ($binnString != '') {
            $this->_binnLoad($binnString);
        }

        return $this;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     *
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->methodsAssignments)) {
            $this->_addVal($this->methodsAssignments[$name], $arguments[0]);
            return $this;
        }

        throw new \Exception("Call to undefined method {$name}");
    }

    /**
     * @param string $binnString
     */
    public function binnOpen($binnString = '')
    {
        if ($binnString != '') {
            $this->_binnLoad($binnString);
        }
    }

    /**
     * Get binary string
     *
     * @return string
     */
    public function getBinnVal()
    {
        $this->calculateSize();

        $this->binnString = '';
        $this->binnString .= pack("C", $this->binnType);

        $this->binnString .= ($this->size <= 127)
            ? pack("C", $this->size)
            : $this->getInt32Binsize($this->size);

        $count = count($this->binnArr);
        $this->binnString .= ($count <= 127)
            ? pack("C", $count)
            : $this->getInt32Binsize($count);

        foreach ($this->binnArr as &$arr) {
            switch ($arr[self::KEY_TYPE]) {
                case self::BINN_BOOL:
                    $this->binnString .= $arr[self::KEY_VAL] ? pack("C", self::BINN_TRUE) : pack("C", self::BINN_FALSE);
                    break;

                case self::BINN_TRUE:
                    $this->binnString .= pack("C", self::BINN_TRUE);
                    break;

                case self::BINN_FALSE:
                    $this->binnString .= pack("C", self::BINN_FALSE);
                    break;

                case self::BINN_UINT8:
                    $this->binnString .= pack("C", self::BINN_UINT8);
                    $this->binnString .= pack("C", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_UINT16:
                    $this->binnString .= pack("C", self::BINN_UINT16);
                    $this->binnString .= pack("n", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_UINT32:
                    $this->binnString .= pack("C", self::BINN_UINT32);
                    $this->binnString .= pack("N", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_UINT64:
                    $this->binnString .= pack("C", self::BINN_UINT64);
                    $this->binnString .= pack("J", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_INT8:
                    $this->binnString .= pack("C", self::BINN_UINT8);
                    $this->binnString .= pack("c", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_INT16:
                    $this->binnString .= pack("C", self::BINN_INT16);
                    $this->binnString .= strrev(pack("s", $arr[self::KEY_VAL]));
                    break;

                case self::BINN_INT32:
                    $this->binnString .= pack("C", self::BINN_INT32);
                    $this->binnString .= strrev(pack("l", $arr[self::KEY_VAL]));
                    break;

                case self::BINN_INT64:
                    $this->binnString .= pack("C", self::BINN_INT64);
                    $this->binnString .= strrev(pack("q", $arr[self::KEY_VAL]));
                    break;

                case self::BINN_STRING:
                    $this->binnString .= pack("C", self::BINN_STRING);

                    if ($arr[self::KEY_SIZE] <= 127) {
                        $this->binnString .= pack("C", $arr[self::KEY_SIZE]);
                    } else {
                        $this->binnString .= $this->getInt32Binsize($arr[self::KEY_SIZE]);
                    }

                    $this->binnString .= pack("a*x", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_LIST:
                    $this->binnString .= $arr[self::KEY_VAL]->getBinnVal();
                    break;
            }
        }

        return $this->binnString;
    }

    /**
     * @param array $array
     * @return string
     */
    public function serialize($array = [])
    {
        if (empty($array)) {
            return $this->getBinnVal();
        }

        $this->binnFree();

        if ($this->isAssoc($array)) {
            throw new InvalidArrayException('Array should be sequential');
        }

        foreach ($array as $item) {
            $type = $this->detectType($item);

            if (($type & self::BINN_STORAGE_CONTAINER) === self::BINN_STORAGE_CONTAINER) {
                $binn = new BinnList();
                $binn->serialize($item);
                $item = $binn;
            }

            $this->_addVal($type, $item);
        }

        return $this->getBinnVal();
    }

    /**
     * @param string $binnString
     * @return array
     */
    public function unserialize($binnString = '')
    {
        if (empty($binnString)) {
            return $this->getBinnArr();
        }

        $this->binnFree();

        $this->binnOpen($binnString);
        return $this->getBinnArr();
    }

    /**
     * @param int   $type
     * @param mixed $value
     */
    private function _addVal($type, $value)
    {
        if (in_array($type,
            [self::BINN_INT64, self::BINN_INT32, self::BINN_INT16,
                self::BINN_UINT64,self::BINN_UINT32, self::BINN_UINT16])
        ) {
            $type = $this->compressInt($type, $value);
        }

        // Data size
        switch ($type) {
            case self::BINN_BOOL:
            case self::BINN_TRUE:
            case self::BINN_FALSE:
                $metaSize = 1;
                $dataSize = 0;
                break;

            case self::BINN_INT8:
            case self::BINN_UINT8:
                $metaSize = 1;
                $dataSize = 1;
                break;

            case self::BINN_INT16:
            case self::BINN_UINT16:
                $metaSize = 1;
                $dataSize = 2;
                break;

            case self::BINN_INT32:
            case self::BINN_UINT32:
                $metaSize = 1;
                $dataSize = 4;
                break;

            case self::BINN_INT64:
            case self::BINN_UINT64:
                $metaSize = 1;
                $dataSize = 8;
                break;

            case self::BINN_STRING:
                $dataSize = strlen($value);

                $metaSize = $dataSize > 127 ? 4 : 1; // size byte
                $metaSize += 2; // type byte + null terminated
                break;

            case self::BINN_LIST:
            case self::BINN_MAP:
            case self::BINN_OBJECT:
            case self::BINN_STORAGE_CONTAINER:
                $dataSize = $value->binnSize();
                $metaSize = 0;
                break;

            default:
                // Unknown type
                return;
                break;
        }

        $this->dataSize += $dataSize;
        $this->metaSize += $metaSize;

        $this->count++;

        $this->binnArr[] = [
            self::KEY_TYPE      => $type,
            self::KEY_VAL       => $value,
            self::KEY_SIZE      => $dataSize
        ];
    }

    /**
     * @param string
     */
    private function _binnLoad($binstring)
    {
        $pos = 1; // Position
        $size_bytes = unpack("C", $binstring[$pos])[1];

        // Size
        if ($size_bytes & 1 << 7) {
            $size_bytes = unpack("N", substr($binstring, $pos, 4))[1];
            $this->size = ($size_bytes &~ (1 << 31)); // Cut bit
            $pos += 4;
        } else {
            $this->size = $size_bytes;
            $pos += 1;
        }

        unset($size_bytes);

        $count_bytes = unpack("C", $binstring[$pos])[1];

        // Size
        if ($count_bytes & 1 << 7) {
            $count_bytes = unpack("N", substr($binstring,$pos, 4))[1];
            $this->count = ($count_bytes &~ (1 << 31)); // Cut bit
            $pos += 4;
        } else {
            $this->count = $count_bytes;
            $pos += 1;
        }

        unset($count_bytes);

        // Data
        $stop_while = false;
        while ($pos < $this->size && !$stop_while) {
            $byte_var_type = @unpack("C", $binstring[$pos])[1];
            $pos += 1;


            // $cur_type = strtotime(base_convert($byte_var_type, 10, 16));

            switch ($byte_var_type) {
                case self::BINN_TRUE:
                    $this->_addVal(self::BINN_BOOL, true);
                    break;

                case self::BINN_FALSE:
                    $this->_addVal(self::BINN_BOOL, false);
                    break;

                case self::BINN_UINT64:
                    $this->_addVal(self::BINN_UINT64, unpack("J", substr($binstring, $pos, 8))[1]);
                    $pos += 8;
                    break;

                case self::BINN_UINT32:
                    $this->_addVal(self::BINN_UINT32, unpack("N", substr($binstring, $pos, 4))[1]);
                    $pos += 4;
                    break;

                case self::BINN_UINT16:
                    $this->_addVal(self::BINN_UINT16, unpack("n", substr($binstring, $pos, 2))[1]);
                    $pos += 2;
                    break;

                case self::BINN_UINT8:
                    $this->_addVal(self::BINN_UINT8, unpack("C", substr($binstring, $pos, 1))[1]);
                    $pos += 1;
                    break;

                case self::BINN_INT8:
                    $this->_addVal(self::BINN_INT8, unpack("c", substr($binstring, $pos, 1))[1]);
                    $pos += 1;
                    break;

                case self::BINN_INT16:
                    $this->_addVal(self::BINN_INT16, unpack("s", strrev(substr($binstring, $pos, 2)))[1]);
                    $pos += 2;
                    break;

                case self::BINN_INT32:
                    $this->_addVal(self::BINN_INT16, unpack("i", strrev(substr($binstring, $pos, 4)))[1]);
                    $pos += 4;
                    break;

                case self::BINN_INT64:
                    $this->_addVal(self::BINN_INT16, unpack("q", strrev(substr($binstring, $pos, 8)))[1]);
                    $pos += 8;
                    break;

                case self::BINN_STRING:
                    $string_size = unpack("C", $binstring[$pos])[1];

                    // Size
                    if ($string_size & 1 << 7) {
                        $string_size = unpack("N", substr($binstring, $pos, 4))[1];
                        $string_size = ($string_size &~ (1 << 31)); // Cut bit
                        $pos += 4;
                    } else {
                        $pos += 1;
                    }

                    $this->_addVal(self::BINN_STRING, unpack("a*", substr($binstring, $pos, $string_size))[1]);
                    $pos += $string_size;
                    $pos += 1; // Null byte
                    break;

                case self::BINN_LIST:
                    $list_size = unpack("C", $binstring[$pos])[1];

                    // Size
                    if ($list_size & 1 << 7) {
                        $list_size = unpack("N", substr($binstring, $pos, 4))[1];
                        $list_size = ($list_size &~ (1 << 31)); // Cut bit
                    }

                    $substring = substr($binstring, $pos-1, $list_size);
                    $this->_addVal(self::BINN_LIST, new BinnList($substring));

                    $pos += ($list_size-1);

                    break;

                default:
                    $stop_while = true;
                    break;
            }

        }
    }
}