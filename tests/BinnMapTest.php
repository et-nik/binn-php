<?php

use Knik\Binn\BinnMap;

/**
 * @covers Knik\Binn\BinnMap<extended>
 */
class BinnMapTest extends \PHPUnit_Framework_TestCase
{
    public function testMapList()
    {
        // https://github.com/liteserver/binn/blob/master/spec.md#a-list-inside-a-map
        /*
        {1: "add", 2: [-12345, 6789]}
        \xE1             // [type] map (container)
        \x1A             // [size] container total size
        \x02             // [count] key/value pairs
        \x00\x00\x00\x01 // key
        \xA0             // [type] = string
        \x03             // [size]
        add\x00          // [data] (null terminated)
        \x00\x00\x00\x02 // key
        \xE0             // [type] list (container)
        \x09             // [size] container total size
        \x02             // [count] items
        \x41             // [type] = int16
        \xCF\xC7         // [data] (-12345)
        \x40             // [type] = uint16
        \x1A\x85         // [data] (6789)
        */

        $binnString = "\xE1\x1A\x02\x00\x00\x00\x01\xA0\x03add\x00\x00\x00\x00\x02\xE0\x09\x02\x41\xCF\xC7\x40\x1A\x85";

        $binn = new BinnMap($binnString);
        $arr = $binn->getBinnArr();
        $this->assertEquals([1 => 'add', 2 => [-12345, 6789]], $arr);
        $this->assertEquals($binnString, $binn->serialize());
    }
}