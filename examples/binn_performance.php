<?php

set_time_limit(0);

include "../vendor/autoload.php";

use Knik\Binn\BinnList;

$write = new BinnList();

$write->addStr("C++ (pronounced as cee plus plus, /ˈsiː plʌs plʌs/) is a general-purpose programming language.");
$write->addStr("If the first bit of size is 0, it uses only 1 byte. So when the data size is up to 127 (0x7F) bytes the size parameter will use only 1 byte. Otherwise a 4 byte size parameter is used, with the msb 1. Leaving us with a high limit of 2 GigaBytes (0x7FFFFFFF).");
$write->addInt16(6459);
$write->addInt16(8459);
$write->addStr("Кириллический текст.");
$write->addStr("Вc9Ka9 6uЛебеpда");
$write->addStr("pack — Упаковывает данные в бинарную строку. Идея этой функции была заимствована из Perl и все коды форматирования работают также. Однако, есть некоторые отсутствующие коды форматирования, как, к примеру, код формата Perl \"u\".");
$write->addInt16(4091243883);
$write->addUint8(233);

// file_put_contents("data.bin", $write->get_binn_val());
// echo "Writed {$write->binn_size()} bytes\n";

// Binn

$bin_string = file_get_contents("data.bin");
$read = new BinnList();

$time_start = microtime(true);
for ($i = 0; $i < 1000000; $i++) {
    $read->binnFree();
    $read->binnOpen($bin_string);
    $read->getBinnArr();
}
echo "Time (Binn): " . (microtime(true) - $time_start) . "\n";

// print_r($read->get_binn_arr());
// file_put_contents("data.json", json_encode($read->get_binn_arr()));

// JSON

$json_string = file_get_contents("data.json");

$time_start = microtime(true);
for ($i = 0; $i < 1000000; $i++) {
    $rjson = json_decode($json_string);
}
echo "Time (Json): " . (microtime(true) - $time_start) . "\n";

/* My results:
 *
 * PHP 5.6.19-0+deb8u1
 * Debian 3.16.7-ckt25-2
 * Intel(R) Celeron(R) CPU B820 @ 1.70GHz 8Gb RAM
 *
 * Time (Binn): 93.416371107101
 * Time (Json): 28.998016119003
 *
 * Time (Binn): 97.379195928574
 * Time (Json): 29.864237070084
 * 
 * Time (Binn): 95.550410985947
 * Time (Json): 31.522666931152
 * 
 *
 */
