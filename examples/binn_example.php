<?php

include "../vendor/autoload.php";

use Knik\Binn\BinnList;

// Write

$array = [4, "Short string", "Long string, long string, long string, long string, long string, long string, long string, long string"];
$writeBinn = new BinnList();
$serialize = $writeBinn->serialize($array);

file_put_contents("test.bin", $serialize);
echo "Writed {$writeBinn->binnSize()} bytes\n";

// Read

$binnString = file_get_contents("test.bin");
$readBinn = new BinnList($binnString);

print_r($readBinn->unserialize()) . PHP_EOL;

$array = [2, true, [123, -456, 789]];

$binn = new BinnList();
$serialized = $binn->serialize($array);
//
$binnString = $serialized;
//
for ($i = 0; $i < strlen($binnString); $i++) {
    echo "\\x" . strtoupper(str_pad(dechex(ord($binnString[$i])), 2, '0', STR_PAD_LEFT));
}


echo PHP_EOL;