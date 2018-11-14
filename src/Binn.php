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
     * Binn constructor
     */
    public function __construct()
    {
        $this->setContainersClasses([
            self::BINN_LIST => BinnList::class,
            self::BINN_MAP => BinnMap::class,
            self::BINN_OBJECT => BinnObject::class,
        ]);
    }

    /**
     * @param array $array
     * @return string
     */
    public function serialize($array = [])
    {
        $this->binnFree();

        foreach ($this->containersClasses as $contanerType => $containersClass)
        {
            if ($containersClass::validArray($array)) {
                $container = new $containersClass();
                return $container->serialize($array);
            }
        }
    }

    /**
     * @param string $binnString
     * @return array|null
     */
    public function unserialize($binnString = '')
    {
        if (empty($binnString)) {
            return $this->getBinnArr();
        }

        $this->binnFree();

        $type = $this->unpack(Binn::BINN_UINT8, $binnString[0]);

        if (array_key_exists($type, $this->containersClasses)) {
            $binnContainer = new $this->containersClasses[$type]($binnString);
            return $binnContainer->unserialize();
        } else {
            return null;
        }
    }
}