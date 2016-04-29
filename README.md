Binn
====

PHP Class for serialize to binary string.

Original Binn Library for C++ - https://github.com/liteserver/binn

Binn Specification: https://github.com/liteserver/binn/blob/master/spec.md

Examples:
```
$write = new Binn();
$write->add_int16(4);
file_put_contents("binn_string.bin", $write->get_binn_val());
```
