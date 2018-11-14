<?php

use PHPUnit\Framework\TestCase;
use Knik\Binn\BinnAbstract;
use Knik\Binn\BinnList;

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

    public function testDetectType()
    {
        $binn = new BinnOver();

        $this->assertEquals($binn::BINN_TRUE, $binn->detectType(true));
        $this->assertEquals($binn::BINN_FALSE, $binn->detectType(false));

        $this->assertEquals($binn::BINN_FLOAT32, $binn->detectType(1.25));
        $this->assertEquals($binn::BINN_FLOAT64, $binn->detectType(31.00000542123925));

        $this->assertEquals($binn::BINN_NULL, $binn->detectType(null));
    }

    public function testDetectInt()
    {
        $binn = new BinnOver();

        $this->assertEquals($binn::BINN_UINT8, $binn->detectInt(0));
        $this->assertEquals($binn::BINN_UINT8, $binn->detectInt(1));
        $this->assertEquals($binn::BINN_UINT8, $binn->detectInt(2));
        $this->assertEquals($binn::BINN_UINT8, $binn->detectInt(255));
        $this->assertEquals($binn::BINN_INT8, $binn->detectInt(-1));
        $this->assertEquals($binn::BINN_INT8, $binn->detectInt(-2));
        $this->assertEquals($binn::BINN_UINT16, $binn->detectInt(256));
        $this->assertEquals($binn::BINN_INT16, $binn->detectInt(-250));
        $this->assertEquals($binn::BINN_UINT32, $binn->detectInt(4294967295));
        $this->assertEquals($binn::BINN_INT32, $binn->detectInt(-2147483648));
        $this->assertEquals($binn::BINN_INT64, $binn->detectInt(-4294967295));
        $this->assertEquals($binn::BINN_UINT64, $binn->detectInt(18446744073709551615));
        $this->assertEquals($binn::BINN_INT64, $binn->detectInt(-9223372036854775808));
    }

    public function testCompressInt()
    {
        $binn = new BinnOver();

        $this->assertEquals($binn::BINN_UINT8, $binn->compressInt($binn::BINN_UINT16, 1));

        // Int -> Uint
        $this->assertEquals($binn::BINN_UINT64, $binn->compressInt($binn::BINN_INT64, $binn::INT64_MAX));
        $this->assertEquals($binn::BINN_UINT32, $binn->compressInt($binn::BINN_INT32, $binn::INT32_MAX));
        $this->assertEquals($binn::BINN_UINT16, $binn->compressInt($binn::BINN_INT16, $binn::INT16_MAX));
        $this->assertEquals($binn::BINN_UINT8, $binn->compressInt($binn::BINN_INT8, $binn::INT8_MAX));

        // Int -> int low
        $this->assertEquals($binn::BINN_INT32, $binn->compressInt($binn::BINN_INT64, $binn::INT32_MIN));
        $this->assertEquals($binn::BINN_INT16, $binn->compressInt($binn::BINN_INT32, $binn::INT16_MIN));
        $this->assertEquals($binn::BINN_INT8, $binn->compressInt($binn::BINN_INT16, $binn::INT8_MIN));
    }

    public function testIsArrayAssoc()
    {
        $this->assertTrue(BinnOver::isArrayAssoc(['hello' => 'world']));
    }

    public function testPack()
    {
        $binn = new BinnOver();

        $this->assertEquals("\x01", $binn->pack($binn::BINN_TRUE));
        $this->assertEquals("\x02", $binn->pack($binn::BINN_FALSE));


        $this->assertNull($binn->pack('Unknown', 'Unknown'));
    }
}

// Make protected methods public
class BinnOver extends BinnList
{
    public function storageType($type)
    {
        return parent::storageType($type);
    }

    public function detectType($value = null)
    {
        return parent::detectType($value);
    }

    public function detectInt($value)
    {
        return parent::detectInt($value);
    }

    public function compressInt($type, $val)
    {
        return parent::compressInt($type, $val);
    }

    public static function isArrayAssoc($arr)
    {
        return parent::isArrayAssoc($arr);
    }

    public function pack($varType, $value = null)
    {
        return parent::pack($varType, $value);
    }

    public function unpack($varType, $value = null)
    {
        return parent::unpack($varType, $value);
    }
}