<?php

use PHPUnit\Framework\TestCase;
use Knik\Binn\BinnList;

/**
 * @covers Knik\Binn\BinnList<extended>
 */
class BinnListTest extends TestCase
{
    static private $stringBinnList = "\xE0\x15\x02\xA0\x05Hello\x00\xA0\x07 World!\x00";

    public function testListInt()
    {
        $binn = new BinnList();

        // https://github.com/liteserver/binn/blob/master/spec.md#a-list-of-3-integers
        $binn->addUint16(123)->addInt16(-456)->addUint16(789);

        $this->assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $binn->getBinnVal());

        // 11 bytes
        $this->assertEquals(11, $binn->binnSize());
    }

    public function testListFloat()
    {
        $float = 12.34567;
        $binn = new BinnList();
        $binn->addFloat($float);
        $binnString = $binn->getBinnVal();

        $binnRead = new BinnList($binnString);
        $arrRead = $binnRead->getBinnArr();

        $this->assertEquals($float, $arrRead[0], '', 0.000001);

        $double = 0.00000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000025;
        $binn = new BinnList();
        $binn->addDouble($double);
        $binnString = $binn->getBinnVal();

        $binnRead = new BinnList($binnString);
        $arrRead = $binnRead->getBinnArr();

        $this->assertEquals($double, $arrRead[0]);
    }

    public function testListString()
    {
        $binn = new BinnList();
        $binn->addStr("Hello")->addStr(' World!');
        $this->assertEquals(self::$stringBinnList, $binn->getBinnVal());

        $this->assertEquals(strlen($binn->getBinnVal()), $binn->binnSize());
    }


    public function testListList()
    {
        $binn = new BinnList();
        $binn->addStr("Hello");

        $binnSubj = new BinnList();
        $binnSubj->addStr("World");

        $binn->addList($binnSubj);

        $this->assertEquals("\xE0\x16\x02\xA0\x05Hello\x00\xE0\x0B\x01\xA0\x05World\x00", $binn->getBinnVal());
    }

    public function testBinnFree()
    {
        $binn = new BinnList();

        $binn->addUint8(123)->addInt16(-456)->addUint16(789);
        $this->assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $binn->getBinnVal());
        $this->assertEquals(11, $binn->binnSize());

        $binn->binnFree();
        $binn->addUint8(512)->addInt16(-521);
        $this->assertEquals("\xE0\x08\x02\x20\x00\x41\xFD\xF7", $binn->getBinnVal());
        $this->assertEquals(8, $binn->binnSize());
    }

    public function testBinnOpen()
    {
        $binn = new BinnList();
        $binn->binnOpen("\xE0\x15\x02\xA0\x05Hello\x00\xA0\x07 World!\x00");
        $this->assertEquals(['Hello', ' World!'], $binn->getBinnArr());

        $binn->binnFree();
        $binn->binnOpen("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15");
        $this->assertEquals([123, -456, 789], $binn->getBinnArr());
    }

    public function testGetBinnArr()
    {
        $binn = new BinnList();
        $binn->addUint8(123)->addInt16(-456)->addUint16(789);
        $this->assertEquals([123, -456, 789], $binn->getBinnArr());
        $this->assertEquals([123, -456, 789], $binn->getBinnArr());
    }

    public function testBigBinn()
    {
        $binn1 = new BinnList();

        $binn1->addInt8(6);
        $binn1->addStr('text-text-text-text-text-text-text-text-text-text-text-tex'); // length 58
        $binn1->addStr('text-text-text-text-text-text-text-text-text-text-text-text'); // length 59
        $binn1->addBool(false);
        $binn1->addBool(true);

        $arr = $binn1->getBinnArr();
        $binnString = $binn1->getBinnVal();

        $binn2 = new BinnList($binnString);
        $arr2 = $binn2->getBinnArr();

        $this->assertEquals($arr, $arr2);
    }

    public function testUnserialize()
    {
        $binn = new BinnList();
        $this->assertEquals(['Hello', ' World!'], $binn->unserialize("\xE0\x15\x02\xA0\x05Hello\x00\xA0\x07 World!\x00"));

        $binn = new BinnList();
        $binn->binnOpen("\xE0\x15\x02\xA0\x05Hello\x00\xA0\x07 World!\x00");
        $this->assertEquals(['Hello', ' World!'], $binn->unserialize());
    }

    public function testSerialize()
    {
        $binn = new BinnList();
        $binnString = $binn->serialize(['Hello', ' World!']);
        $this->assertEquals(self::$stringBinnList, $binnString);

        $binnString = $binn->serialize([123, -456, 789]);
        $this->assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $binnString);

        $arrayWithFloat = [458, 5.2349, 94.005000000000000058];
        $binnString = $binn->serialize($arrayWithFloat);
        $binnArray = $binn->unserialize($binnString);

        $this->assertEquals($arrayWithFloat, $binnArray);

        $binn2 = new BinnList();
        $binn2->addUint8(512)->addInt16(-521);
        $this->assertEquals("\xE0\x08\x02\x20\x00\x41\xFD\xF7", $binn2->serialize());
    }

    public function testSerializeBigSize()
    {
        $array = [];
        for ($i = 0; $i < 512; $i++) {
            $array[] = rand(BinnList::INT64_MIN, BinnList::INT64_MAX);
        }

        $binn1 = new BinnList;
        $serialized = $binn1->serialize($array);

        $binn2 = new BinnList;
        $binn2->binnOpen($serialized);
        $unserialized = $binn2->unserialize();

        $this->assertEquals($array, $unserialized);
    }

    public function testSerializeList()
    {
        $binn = new BinnList();
        $binnString = $binn->serialize(['Hello', ['World']]);

        $this->assertEquals("\xE0\x16\x02\xA0\x05Hello\x00\xE0\x0B\x01\xA0\x05World\x00", $binnString);
    }

    /**
     * @expectedException Knik\Binn\Exceptions\InvalidArrayException
     */
    public function testSerializeInvalid()
    {
        $binn = new BinnList();
        $binn->serialize(['Hello', 'assoc_key' => 'World']);
    }

    /**
     * @expectedException \Exception
     */
    public function testInvalidMethod()
    {
        $binn = new BinnList();
        $binn->addUnknown('azaza');
    }

    public function testValidArray()
    {
        $this->assertTrue(BinnList::validArray([0, 1, 2]));
        $this->assertFalse(BinnList::validArray([1 => 0, 2 => 2]));
        $this->assertFalse(BinnList::validArray(['key' => 'val']));
    }
}