<?php

include "../Binn.php";

use knik\Binn;

// Write

$write = new Binn();

$write->add_int16(4);
$write->add_str("Short string");
$write->add_str("If the first bit of size is 0, it uses only 1 byte. So when the data size is up to 127 (0x7F) bytes the size parameter will use only 1 byte. Otherwise a 4 byte size parameter is used, with the msb 1. Leaving us with a high limit of 2 GigaBytes (0x7FFFFFFF).");
$write->add_uint8(1);

// socket_write($socket, $write->get_binn_val(), $write->binn_size());
file_put_contents("test.bin", $write->get_binn_val());
echo "Writed {$write->binn_size()} bytes\n";

// Read

// $bin_string = $write->get_binn_val();
$bin_string = file_get_contents("test.bin");
$read = new Binn();
$read->binn_open($bin_string);

print_r($read->get_binn_arr());
