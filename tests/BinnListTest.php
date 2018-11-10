<?php

use Knik\Binn\BinnList;

/**
 * @covers Knik\Binn\BinnList<extended>
 */
class BinnListTest extends \PHPUnit_Framework_TestCase
{
    public function testListInt()
    {
        $binn = new BinnList();

        // https://github.com/liteserver/binn/blob/master/spec.md#a-list-of-3-integers
        $binn->addUint16(123)->addInt16(-456)->addUint16(789);

        $this->assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $binn->getBinnVal());

        // 11 bytes
        $this->assertEquals(11, $binn->binnSize());
    }

    public function testListString()
    {
        $binn = new BinnList();
        $binn->addStr("Hello")->addStr(' World!');
        $this->assertEquals("\xE0\x15\x02\xA0\x05Hello\x00\xA0\x07 World!\x00", $binn->getBinnVal());

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

        $arr = $binn1->getBinnArr();
        $binnString = $binn1->getBinnVal();

        $binn2 = new BinnList($binnString);
        $arr2 = $binn2->getBinnArr();

        $this->assertEquals($arr, $arr2);
    }
}