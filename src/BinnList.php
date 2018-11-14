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
 * @method BinnList addFloat(string $value)
 * @method BinnList addDouble(string $value)
 * @method BinnList addStr(string $value)
 * @method BinnList addList(Binn $value)
 * @method BinnList addMap(Binn $value)
 * @method BinnList addObject(Binn $value)
 *
 */
class BinnList extends BinnAbstract
{
    protected $binnType = self::BINN_LIST;

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
        'addFloat'     => self::BINN_FLOAT32,
        'addDouble'    => self::BINN_FLOAT64,
        'addStr'       => self::BINN_STRING,
        'addList'      => self::BINN_LIST,
        'addMap'       => self::BINN_MAP,
        'addObject'    => self::BINN_OBJECT,
    ];

    public function __construct($binnString = '')
    {
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
        $this->binnString .= $this->pack(self::BINN_UINT8, $this->binnType);

        $this->binnString .= $this->packSize($this->size);

        $count = count($this->binnArr);
        $this->binnString .= $this->packSize($count);

        foreach ($this->binnArr as &$arr) {
            $type = $arr[self::KEY_TYPE];
            $storageType = $this->storageType($type);

            if ($type === self::BINN_BOOL) {
                $this->binnString .= $arr[self::KEY_VAL]
                    ? $this->packType(self::BINN_TRUE)
                    : $this->packType(self::BINN_FALSE);

                continue;
            }

            if ($storageType === self::BINN_STORAGE_QWORD
                || $storageType === self::BINN_STORAGE_DWORD
                || $storageType === self::BINN_STORAGE_WORD
                || $storageType === self::BINN_STORAGE_BYTE
            ) {
                $this->binnString .= $this->packType($arr[self::KEY_TYPE]);
                $this->binnString .= $this->pack($arr[self::KEY_TYPE], $arr[self::KEY_VAL]);
            } else if ($storageType === self::BINN_STORAGE_NOBYTES) {
                $this->binnString .= $this->packType($arr[self::KEY_TYPE]);
            } else if ($storageType === self::BINN_STORAGE_STRING) {
                $this->binnString .= $this->packType(self::BINN_STRING);
                $this->binnString .= $this->packSize($arr[self::KEY_SIZE]);
                $this->binnString .= $this->pack(self::BINN_STRING, $arr[self::KEY_VAL]);
                $this->binnString .= $this->pack(self::BINN_NULL);
            } else if ($storageType === self::BINN_STORAGE_CONTAINER) {
                $this->binnString .= $arr[self::KEY_VAL]->getBinnVal();
            }
        }

        return $this->binnString;
    }

    /**
     * Check is valid array to serialize
     *
     * @param $array
     * @return bool
     */
    public static function validArray($array)
    {
        $array = (array)$array;
        if (self::isArrayAssoc($array)) {
            return false;
        }

        return true;
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

        if ($this->isArrayAssoc($array)) {
            throw new InvalidArrayException('Array should be sequential');
        }

        foreach ($array as $item) {
            $type = $this->detectType($item);
            $storageType = $this->storageType($type);

            if ($storageType === self::BINN_STORAGE_CONTAINER) {
                foreach ($this->containersClasses as $contanerType => $containersClass)
                {
                    if ($containersClass::validArray($item)) {
                        $container = new $containersClass();
                        $container->serialize($item);
                        $item = $container;
                        break;
                    }
                }
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

        $size = $this->getTypeSize($type, $value);

        $this->dataSize += $size['data'];
        $this->metaSize += $size['meta'];

        $this->count++;

        $this->binnArr[] = [
            self::KEY_TYPE      => $type,
            self::KEY_VAL       => $value,
            self::KEY_SIZE      => $size['data']
        ];
    }

    /**
     * @param string
     */
    private function _binnLoad($binnString)
    {
        $pos = 1; // Position
        $sizeBytes = $this->unpack(self::BINN_UINT8, $binnString[$pos]);

        // Size
        if ($sizeBytes & 1 << 7) {
            $sizeBytes = $this->unpack(self::BINN_UINT32, substr($binnString, $pos, 4));
            $this->size = ($sizeBytes &~ (1 << 31)); // Cut bit
            $pos += 4;
        } else {
            $this->size = $sizeBytes;
            $pos += 1;
        }

        unset($sizeBytes);

        $countBytes = $this->unpack(self::BINN_UINT8, $binnString[$pos]);

        // Size
        if ($countBytes & 1 << 7) {
            $countBytes = $this->unpack(self::BINN_UINT32, substr($binnString, $pos, 4));
            $this->count = ($countBytes &~ (1 << 31)); // Cut bit
            $pos += 4;
        } else {
            $this->count = $countBytes;
            $pos += 1;
        }

        unset($countBytes);

        // Data
        $stop_while = false;
        while ($pos < $this->size && !$stop_while) {
            $varType = $this->unpack(self::BINN_UINT8, $binnString[$pos]);
            $varStorageType = $this->storageType($varType);
            $pos += 1;

            if ($varStorageType === self::BINN_STORAGE_QWORD
                || $varStorageType === self::BINN_STORAGE_DWORD
                || $varStorageType === self::BINN_STORAGE_WORD
                || $varStorageType === self::BINN_STORAGE_BYTE
                || $varStorageType === self::BINN_STORAGE_NOBYTES
            ) {
                $varSize = $this->getTypeSize($varType);
                $val = $this->unpack($varType, substr($binnString, $pos, $varSize['data']));
                $this->_addVal($varType, $val);
                $pos += $varSize['data'];

            } else if ($varStorageType === self::BINN_STRING ) {
                $stringSize = $this->unpack(self::BINN_UINT8, $binnString[$pos]);

                // Size
                if ($stringSize & 1 << 7) {
                    $stringSize = $this->unpack(self::BINN_UINT32, substr($binnString, $pos, 4));
                    $stringSize = ($stringSize &~ (1 << 31)); // Cut bit
                    $pos += 4;
                } else {
                    $pos += 1;
                }

                $this->_addVal(self::BINN_STRING, $this->unpack(
                    self::BINN_STRING,
                    substr($binnString, $pos, $stringSize)
                ));

                $pos += $stringSize;
                $pos += 1; // Null byte
            } else if ($varStorageType === self::BINN_STORAGE_CONTAINER) {
                $list_size = $this->unpack(self::BINN_UINT8, $binnString[$pos]);;

                // Size
                if ($list_size & 1 << 7) {
                    $list_size = $this->unpack(self::BINN_UINT32, substr($binnString, $pos, 4));
                    $list_size = ($list_size &~ (1 << 31)); // Cut bit
                }

                $substring = substr($binnString, $pos-1, $list_size);

                foreach ($this->containersClasses as $containerType => $containersClass) {
                    if ($containerType === $varType) {
                        $container = new $containersClass($substring);
                        $this->_addVal($varType, $container);
                        break;
                    }
                }

                $pos += ($list_size-1);
            } else {
                $stop_while = true;
            }
        }
    }
}