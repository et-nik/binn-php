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

        // Null
        $binnString = $binn->serialize(null);
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

        // Empty
        $binn = new Binn;
        $this->assertCount(0, $binn->unserialize());

        // String
        $binn = new Binn;
        $this->assertEquals(null, $binn->unserialize("\xA0\x05Hello\x00"));
    }

    public function testSerializeUnserializeBigCount()
    {
        $array = [];
        for ($i = 0; $i < 512; $i++) {
            $array[] = rand(-256, 256);
        }

        $array[] = implode('', $array);

        $binn1 = new Binn;
        $serialized = $binn1->serialize($array);

        $binn2 = new Binn;
        $unserialized = $binn2->unserialize($serialized);

        $this->assertEquals($array, $unserialized);
    }

    public function testSerializeTypes()
    {
        $binn1 = new Binn;
        $array = [1 => true, 2 => false, 3 => 'a', 4 => 'abc', 8 => 0, 12 => 1, 13 => -1, 17 => $binn1::INT8_MIN,
            19 => $binn1::INT16_MIN, 20 => $binn1::INT32_MIN, 24 => -9223372036854775807, 26 => Binn::INT64_MAX,
            28 => 2.3, 32 => -2.3, 55 => 45.0034525, 56 => -45.0034525, 57 => null];

        $serialized = $binn1->serialize($array);
        file_put_contents('test.bin', $serialized);

        $binn2 = new Binn;
        $unserialized = $binn2->unserialize($serialized);

        $this->assertEquals($array, $unserialized, '', 0.000001);
    }

    public function testObject()
    {
        $array = ['name' => 'knik/binn', 'description' => 'Serialize to binary string.', 'keywords' => ["serialize", "bin"]];
        $object = (object) $array;

        $binn1 = new Binn;
        $arraySerialized = $binn1->serialize($array);
        $objectSerialized = $binn1->serialize($object);

        $this->assertEquals($arraySerialized, $objectSerialized);
    }
}