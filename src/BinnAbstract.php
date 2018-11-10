<?php

namespace Knik\Binn;


abstract class BinnAbstract
{
    // Consts from original C++ Library
    const BINN_LIST         = 0xE0;
    const BINN_MAP          = 0xE1;
    const BINN_OBJECT       = 0xE2;

    const BINN_UINT8        = 0x20;
    const BINN_INT8         = 0x21;
    const BINN_UINT16       = 0x40;
    const BINN_INT16        = 0x41;
    const BINN_UINT32       = 0x60;
    const BINN_INT32        = 0x61;
    const BINN_UINT64       = 0x80;
    const BINN_INT64        = 0x81;
    const BINN_STRING       = 0xA0;

    const BINN_BOOL         = 0x80061;

    const BINN_STORAGE_NOBYTES      = 0x00;
    const BINN_STORAGE_BYTE         = 0x20;  //  8 bits
    const BINN_STORAGE_WORD         = 0x40;  // 16 bits -- the endianess (byte order) is automatically corrected
    const BINN_STORAGE_DWORD        = 0x60;  // 32 bits -- the endianess (byte order) is automatically corrected
    const BINN_STORAGE_QWORD        = 0x80;  // 64 bits -- the endianess (byte order) is automatically corrected
    const BINN_STORAGE_STRING       = 0xA0;  // Are stored with null termination
    const BINN_STORAGE_BLOB         = 0xC0;
    const BINN_STORAGE_CONTAINER    = 0xE0;

    const BINN_NULL                 = 0x00;
    const BINN_TRUE                 = 0x01;
    const BINN_FALSE                = 0x02;

    const UINT8_MAX                 = 255;
    const UINT16_MAX                = 65535;
    const UINT32_MAX                = 4294967295;
    const UINT64_MAX                = 18446744073709551615;

    const INT8_MIN                  = -128;
    const INT8_MAX                  = 127;
    const INT16_MIN                 = -32768;
    const INT16_MAX                 = 32767;
    const INT32_MIN                 = -2147483648;
    const INT32_MAX                 = 2147483647;
    const INT64_MIN                 = -9223372036854775808;
    const INT64_MAX                 = 9223372036854775807;

    const MIN_BINN_SIZE             = 3;

    // PHP Library consts
    const KEY_TYPE                 = 0;
    const KEY_VAL                  = 1;
    const KEY_SIZE                 = 2;
    const KEY_KEY                  = 3;

    /**
     * Binn object type: self::BINN_LIST, self::BINN_MAP, self::BINN_OBJECT
     *
     * @var int $binnType
     * @access protected
     */
    protected $binnType;

    /**
     * @var string
     */
    protected $binnClass = null;

    /**
     * Count elements in object
     *
     * @var int
     * @access protected
     */
    protected $count        = 0;

    /**
     * Data size in bytes
     *
     * @var int
     * @access protected
     */
    protected $dataSize    = 0;

    /**
     * Meta size in bytes
     *
     * @var int
     */
    protected $metaSize    = self::MIN_BINN_SIZE;

    /**
     * Size bin string in bytes
     *
     * @var int
     * @access protected
     */
    protected $size         = 0;

    /**
     * Bin string
     *
     * @var string
     * @access protected
     */
    protected $binnString     = "";
}