<?php

use PHPUnit\Framework\TestCase;
use \Knik\Binn\Binn;
use \Knik\Binn\BinnList;
use \Knik\Binn\BinnMap;
use \Knik\Binn\BinnObject;

/**
 * @covers Knik\Binn\Binn<extended>
 */
class BinnTest extends TestCase
{
    public function testSerialize()
    {
        $binn = new Binn;

        // List
        $binnString = $binn->serialize([123, -456, 789]);
        $this->assertEquals("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15", $binnString);

        // Map
        $binnString = $binn->serialize([1 => 'add', 2 => [-12345, 6789]]);
        $this->assertEquals("\xE1\x1A\x02\x00\x00\x00\x01\xA0\x03add\x00\x00\x00\x00\x02\xE0\x09\x02\x41\xCF\xC7\x40\x1A\x85", $binnString);

        // Object
        $binnString = $binn->serialize(['hello' => 'world']);
        $this->assertEquals("\xE2\x11\x01\x05hello\xA0\x05world\x00", $binnString);
    }

    public function testUnserialize()
    {
        $binn = new Binn;

        // List
        $array = $binn->unserialize("\xE0\x0B\x03\x20\x7B\x41\xFE\x38\x40\x03\x15");
        $this->assertEquals([123, -456, 789], $array);

        // Map
        $array = $binn->unserialize("\xE1\x1A\x02\x00\x00\x00\x01\xA0\x03add\x00\x00\x00\x00\x02\xE0\x09\x02\x41\xCF\xC7\x40\x1A\x85");
        $this->assertEquals([1 => 'add', 2 => [-12345, 6789]], $array);

        // Object
        $binnString = $binn->unserialize("\xE2\x11\x01\x05hello\xA0\x05world\x00");
        $this->assertEquals(['hello' => 'world'], $binnString);
    }
}