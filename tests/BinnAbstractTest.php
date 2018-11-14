<?php

use PHPUnit\Framework\TestCase;
use Knik\Binn\BinnAbstract;
use Knik\Binn\BinnList;

class BinnOver extends BinnList
{
    public function storageType($type)
    {
        return parent::storageType($type);
    }
}

/**
 * @covers Knik\Binn\BinnAbstract<extended>
 */
class BinnAbstractTest extends TestCase
{
    public function testStorageType()
    {
        $binn = new BinnOver();

        $this->assertEquals($binn::BINN_STORAGE_BYTE, $binn->storageType($binn::BINN_UINT8));
        $this->assertEquals($binn::BINN_STORAGE_WORD, $binn->storageType($binn::BINN_UINT16));
        $this->assertEquals($binn::BINN_STORAGE_DWORD, $binn->storageType($binn::BINN_UINT32));
        $this->assertEquals($binn::BINN_STORAGE_QWORD, $binn->storageType($binn::BINN_UINT64));
        $this->assertEquals($binn::BINN_STORAGE_STRING, $binn->storageType($binn::BINN_STRING));

        $this->assertEquals($binn::BINN_STORAGE_CONTAINER, $binn->storageType($binn::BINN_LIST));
        $this->assertEquals($binn::BINN_STORAGE_CONTAINER, $binn->storageType($binn::BINN_MAP));
        $this->assertEquals($binn::BINN_STORAGE_CONTAINER, $binn->storageType($binn::BINN_OBJECT));
    }
}