<?php

namespace Knik\Binn;

use Knik\Binn\Contracts\Container;

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
 * @method BinnList addMap(Binn $value)
 * @method BinnList addObject(Binn $value)
 *
 */
class BinnList extends Binn implements Container
{
    protected $binnType = self::BINN_LIST;

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

    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->methodsAssignments)) {
            $this->addVal($arguments[0]);
            return $this;
        }

        throw new \Exception("Call to undefined method {$name}");
    }

    public function addList(BinnList $list): void
    {
        $this->addVal($list->toArray());
    }

    private function addVal($value): void
    {
        $this->items[] = $value;
    }
}
