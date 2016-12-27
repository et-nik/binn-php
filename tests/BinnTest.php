<?php

use PHPUnit\Framework\TestCase;
use knik\Binn;

class BinnTest extends TestCase
{
    public function testListInt()
    {
        $binn = new Binn();

        // https://github.com/liteserver/binn/blob/master/spec.md#a-list-of-3-integers
        $binn->binn_list()->add_uint8(123)->add_int16(-456)->add_uint16(789);

        $this->assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $binn->get_binn_val());

        // 11 bytes
        $this->assertEquals(11, $binn->binn_size());
    }

    public function testListString()
    {
        $binn = new Binn();
        $binn->binn_list()->add_str("Hello")->add_str(' World!');
        $this->assertEquals("\xE0\x15\x02\xA0\x05Hello\x00\xA0\x07 World!\x00", $binn->get_binn_val());
    }

    public function testBinnFree()
    {
        $binn = new Binn();

        $binn->binn_list()->add_uint8(123)->add_int16(-456)->add_uint16(789);
        $this->assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $binn->get_binn_val());
        $this->assertEquals(11, $binn->binn_size());

        $binn->binn_free();
        $binn->binn_list()->add_uint8(512)->add_int16(-521);
        $this->assertEquals("\xE0\x08\x02\x20\x00\x41\xFD\xF7", $binn->get_binn_val());
        $this->assertEquals(8, $binn->binn_size());
    }

    public function testBinnOpen()
    {
        $binn = new Binn();
        $binn->binn_open("\xE0\x15\x02\xA0\x05Hello\x00\xA0\x07 World!\x00");
        $this->assertEquals(['Hello', ' World!'], $binn->get_binn_arr());

        $binn->binn_free();
        $binn->binn_open("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15");
        $this->assertEquals([123, -456, 789], $binn->get_binn_arr());
    }

    public function testGetBinnArr()
    {
        $binn = new Binn();
        $binn->binn_list()->add_uint8(123)->add_int16(-456)->add_uint16(789);
        $this->assertEquals([123, -456, 789], $binn->get_binn_arr());
    }
}