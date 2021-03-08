<?php

namespace Knik\Binn;

use Knik\Binn\Decoder\BinnDecode;
use Knik\Binn\Decoder\DecoderCollectionFactory;
use Knik\Binn\Encoder\BinnEncode;
use Knik\Binn\Encoder\EncoderCollectionFactory;

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

    const BINN_FLOAT32      = 0x62;  // (DWORD)
    const BINN_FLOAT64      = 0x82;  // (QWORD)
    const BINN_FLOAT        = self::BINN_FLOAT32;

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

    const BINN_STORAGE_MASK         = 0xE0;
    const BINN_TYPE_MASK            = 0x0F;

    const BINN_STORAGE_HAS_MORE     = 0x10;

    const BINN_JPEG                 = 0xD001;
    const BINN_GIF                  = 0xD002;
    const BINN_PNG                  = 0xD003;
    const BINN_BMP                  = 0xD004;

    const MIN_BINN_SIZE             = 3;

    const BINN_MAX_ONE_BYTE_SIZE    = 127;

    /**
     * Binn object type: self::BINN_LIST, self::BINN_MAP, self::BINN_OBJECT
     */
    protected $binnType = self::BINN_NULL;

    /** @var BinnEncode */
    protected $encoder;

    /** @var BinnDecode */
    protected $decoder;

    public function __construct(
        ?BinnEncode $encoder = null,
        ?BinnDecode $decoder = null
    ) {
        if ($encoder === null) {
            $factory = new EncoderCollectionFactory();
            $this->encoder = new BinnEncode($factory->getCollection());
        } else {
            $this->encoder = $encoder;
        }

        if ($decoder === null) {
            $factory = new DecoderCollectionFactory();
            $this->decoder = $decoder ?? new BinnDecode($factory->getCollection());
        } else {
            $this->decoder = $decoder;
        }
    }
}
