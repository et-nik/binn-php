<?php
/**
 * Binn. Serialize to bin string.
 * Binn Specification: https://github.com/liteserver/binn/blob/master/spec.md
 *
 * Note! This class not support Map and Object, only List support. Sorry, i am working on this.
 *
 * Original Binn Library for C++ - https://github.com/liteserver/binn
 * 
 *
 * @author      Nikita Kuznetsov (NiK)
 * @copyright   Copyright (c) 2016, Nikita Kuznetsov (nikita.hldm@gmail.com)
 * @license     GNU GPL
 * @link        http://www.gameap.ru
 *
 */

namespace knik;

class Binn {

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

    // PHP Library consts
    const KEY_TYPE                 = 0;
    const KEY_VAL                  = 1;
    const KEY_SIZE                 = 2;

    /**
     * @var array
     */
    private $methods_assignments = [
        'add_bool'      => self::BINN_BOOL,
        'add_uint8'     => self::BINN_UINT8,
        'add_uint16'    => self::BINN_UINT16,
        'add_uint32'    => self::BINN_UINT32,
        'add_uint64'    => self::BINN_UINT64,
        'add_int8'      => self::BINN_INT8,
        'add_int16'     => self::BINN_INT16,
        'add_int32'     => self::BINN_INT32,
        'add_int64'     => self::BINN_INT64,
        'add_str'       => self::BINN_STRING,
        'add_list'      => self::BINN_LIST,
        'add_map'       => self::BINN_MAP,
        'add_object'    => self::BINN_OBJECT,
    ];

    /**
     * Binn object type: self::BINN_LIST, self::BINN_MAP, self::BINN_OBJECT
     *
     * @var int $binn_type
     * @access protected
     */
    protected $binn_type;

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
    protected $data_size    = 0;

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
    protected $binn_string     = "";

    /**
     * Object elements
     * 
     * @var array
     * @access protected
     */
    protected $binn_arr = [];

    // -----------------------------------------------------------------

    public function __construct($binstring = '')
    {
        $this->binn_list();

        if ($binstring != '') {
            $this->_binn_load($binstring);
        }
    }

    // -----------------------------------------------------------------

    /**
     * @param int   $type
     * @param mix   $val
     *
     * @return int  $type2
     * 
     */
    protected function compress_int($type, $val)
    {
        $type2 = $type;

        if ($val >= 0) {
            // Convert to unsigned
            switch ($type) {
                case self::BINN_INT64:
                    $type = self::BINN_UINT64;
                    break;

                case self::BINN_INT32:
                    $type = self::BINN_UINT32;
                    break;

                case self::BINN_INT16:
                    $type = self::BINN_UINT16;
                    break;
            }
        }
        
        if (in_array($type, [self::BINN_INT64, self::BINN_INT32, self::BINN_INT16])) {
            // Signed
            if ($val >= self::INT8_MIN) {
                $type2 = self::BINN_INT8;
            }
            elseif ($val >= self::INT16_MIN) {
                $type2 = self::BINN_INT16;
            }
            elseif ($val >= self::INT32_MIN) {
                $type2 = self::BINN_INT32;
            }
        }

        if (in_array($type, [self::BINN_UINT64, self::BINN_UINT32, self::BINN_UINT16])) {
            // Unsigned

            if ($val <= self::UINT8_MAX) {
                $type2 = self::BINN_UINT8;
            }
            elseif ($val <= self::UINT16_MAX) {
                $type2 = self::BINN_UINT16;
            }
            elseif ($val <= self::UINT32_MAX) {
                $type2 = self::BINN_UINT32;
            }
        }

        return $type2;
    }

    // -----------------------------------------------------------------

    public function binn_free()
    {
        $this->binn_type = self::BINN_STORAGE_NOBYTES;
    
        $this->count        = 0;
        $this->data_size    = 0;
        $this->size         = 0;
        $this->binn_string     = "";

        $this->sub_objects  = [];
        $this->binn_arr     = [];

        return $this;
    }

    // -----------------------------------------------------------------

    /**
     * @param string @bindstring
     */
    public function binn_open($binstring = "")
    {
        $this->_binn_load($binstring);
        return $this;
    }

    // -----------------------------------------------------------------

    /**
     *
     *  @return int
     */
    private function _calculate_size()
    {
        $size = 1; // type

        // Size
        if ($this->data_size > 127) {
            $size += 4; 
        } else {
            $size += 1;
        }

        // Count size
        $arr_count = count($this->binn_arr);
        if ($arr_count > 127) {
            $size += 4;
        }
        else {
            $size += 1;
        }

        // Define types var
        $size += $arr_count;

        // Data size
        foreach ($this->binn_arr as &$arr) {
            if ($arr[self::KEY_TYPE] == self::BINN_STRING) {
                $size += $arr[self::KEY_SIZE] <= 127 ? $arr[self::KEY_SIZE]+2 : $arr[self::KEY_SIZE]+5; // Size Byte + NULL Byte
            }
            else {
                $size += $arr[self::KEY_SIZE];
            }
        }

        $this->size = $size;
        return $this->size;
    }

    // -----------------------------------------------------------------

    /**
     * @param int   $type
     * @param mixed $value
     */
    private function _add_val($type, $value)
    {
        if (in_array($type,
                [self::BINN_INT64, self::BINN_INT32, self::BINN_INT16,
                self::BINN_UINT64,self::BINN_UINT32, self::BINN_UINT16])
        ){
            $type = $this->compress_int($type, $value);
        }

        // Size
        switch ($type) {
            case self::BINN_BOOL:
                $size = 1;
                break;
                
            case self::BINN_UINT8:
                $size = 1;
                break;
                
            case self::BINN_UINT16:
                $size = 2;
                break;
                
            case self::BINN_UINT32:
                $size = 4;
                break;
                
            case self::BINN_UINT64:
                $size = 8;
                break;
                
            case self::BINN_INT8:
                $size = 1;
                break;
                
            case self::BINN_INT16:
                $size = 2;
                break;
                
            case self::BINN_INT32:
                $size = 4;
                break;
                
            case self::BINN_INT64:
                $size = 8;
                break;
                
            case self::BINN_STRING:
                $size = strlen($value);
                break;

            case self::BINN_LIST:
                $size = $value->binn_size();
                break;
        }

        $this->data_size += $size;
        $this->count++;

        $this->binn_arr[] = [
            self::KEY_TYPE      => $type,
            self::KEY_VAL       => $value,
            self::KEY_SIZE      => $size
        ];
    }

    // -----------------------------------------------------------------

    /**
     *
     *  @return array
     */
    public function get_binn_arr()
    {
        $return = [];

        foreach ($this->binn_arr as &$arr) {
            switch ($arr[self::KEY_TYPE]) {
                case self::BINN_LIST:
                    $return[] = $arr[self::KEY_VAL]->get_binn_arr();
                    break;

                case self::BINN_BOOL:
                case self::BINN_TRUE:
                case self::BINN_FALSE:
                case self::BINN_INT64:
                case self::BINN_UINT64:
                case self::BINN_INT32:
                case self::BINN_UINT32:
                case self::BINN_INT16:
                case self::BINN_UINT16:
                case self::BINN_INT8:
                case self::BINN_UINT8:
                case self::BINN_STRING:
                    $return[] = $arr[self::KEY_VAL];
                    break;
            }
        }

        return $return;
    }

    // -----------------------------------------------------------------

    /**
     * @return int
     */
    public function binn_size()
    {
        $this->_calculate_size();
        return $this->size;
    }

    // -----------------------------------------------------------------

    /**
     *
     * @param int $int_val
     *
     * @return string   HEX string
     */
    private function _get_int32_binsize($int_val = 0)
    {
        $int_val = ($int_val | (1 << 31)); // Add byte
        return pack("N", $int_val);
    }

    // -----------------------------------------------------------------

    /**
     * Get binary string
     *
     * @return string
     */
    public function get_binn_val()
    {
        $this->_calculate_size();
        
        $this->binn_string .= pack("C", $this->binn_type);
        
        $this->binn_string .= ($this->size <= 127)
            ? pack("C", $this->size)
            : $this->_get_int32_binsize($this->size);

        $count = count($this->binn_arr);
        $this->binn_string .= ($count <= 127)
            ? pack("C", $count)
            : $this->_get_int32_binsize($count);

        foreach ($this->binn_arr as &$arr) {
            switch ($arr[self::KEY_TYPE]) {
                case self::BINN_BOOL:
                    $this->binn_string .= $arr[self::KEY_VAL] ? pack("C", self::BINN_TRUE) : pack("C", self::BINN_FALSE);
                    break;
                    
                case self::BINN_TRUE:
                    $this->binn_string .= pack("C", self::BINN_TRUE);
                    break;
                    
                case self::BINN_FALSE:
                    $this->binn_string .= pack("C", self::BINN_FALSE);
                    break;
                    
                case self::BINN_UINT8:
                    $this->binn_string .= pack("C", self::BINN_UINT8);
                    $this->binn_string .= pack("C", $arr[self::KEY_VAL]);
                    break;
                    
                case self::BINN_UINT16:
                    $this->binn_string .= pack("C", self::BINN_UINT16);
                    $this->binn_string .= pack("n", $arr[self::KEY_VAL]);
                    break;
                    
                case self::BINN_UINT32:
                    $this->binn_string .= pack("C", self::BINN_UINT32);
                    $this->binn_string .= pack("N", $arr[self::KEY_VAL]);
                    break;
                    
                case self::BINN_UINT64:
                    $this->binn_string .= pack("C", self::BINN_UINT64);
                    $this->binn_string .= pack("J", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_INT8:
                    $this->binn_string .= pack("C", self::BINN_UINT8);
                    $this->binn_string .= pack("c", $arr[self::KEY_VAL]);
                    break;
                    
                case self::BINN_INT16:
                    $this->binn_string .= pack("C", self::BINN_INT16);
                    $this->binn_string .= strrev(pack("s", $arr[self::KEY_VAL]));
                    break;
                    
                case self::BINN_INT32:
                    $this->binn_string .= pack("C", self::BINN_INT32);
                    $this->binn_string .= strrev(pack("l", $arr[self::KEY_VAL]));
                    break;
                    
                case self::BINN_INT64:
                    $this->binn_string .= pack("C", self::BINN_INT64);
                    $this->binn_string .= strrev(pack("q", $arr[self::KEY_VAL]));
                    break;

                case self::BINN_STRING:
                    $this->binn_string .= pack("C", self::BINN_STRING);

                    if ($arr[self::KEY_SIZE] <= 127) {
                        $this->binn_string .= pack("C", $arr[self::KEY_SIZE]);
                    } else {
                        $this->binn_string .= $this->_get_int32_binsize($arr[self::KEY_SIZE]);
                    }
                    
                    $this->binn_string .= pack("a*x", $arr[self::KEY_VAL]);
                    break;

                case self::BINN_LIST:
                    $this->binn_string .= $arr[self::KEY_VAL]->get_binn_val();
                    break;
            }
        }

        return $this->binn_string;
    }

    // -----------------------------------------------------------------

    /**
     * @param string $name
     * @param mixed $arguments
     */
    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->methods_assignments)) {
            $this->_add_val($this->methods_assignments[$name], $arguments[0]);
            return $this;
        }

        throw new \Exception("Call to undefined method {$name}");
    }

    // -----------------------------------------------------------------

    public function binn_list()
    {
        $this->binn_type = self::BINN_LIST;
        return $this;
    }

    // -----------------------------------------------------------------

    /**
     * @param string
     */
    private function _binn_load($binstring)
    {
        $pos = 1; // Позиция
        $size_bytes = unpack("C", $binstring[$pos])[1];

        // Size
        if ($size_bytes & 1 << 7) {
            $size_bytes = unpack("N", substr($binstring, $pos, 4))[1];
            $this->size = ($size_bytes &~ (1 << 31)); // Cut bit
            $pos += 4;
        } else {
            $this->size = $size_bytes;
            $pos += 1;
        }

        unset($size_bytes);

        $count_bytes = unpack("C", $binstring[$pos])[1];

        // Size
        if ($count_bytes & 1 << 7) {
            $count_bytes = unpack("N", substr($binstring,$pos, 4))[1];
            $this->count = ($count_bytes &~ (1 << 31)); // Cut bit
            $pos += 4;
        } else {
            $this->count = $count_bytes;
            $pos += 1;
        }

        unset($count_bytes);

        // Data
        $stop_while = false;
        while ($pos < $this->size && !$stop_while) {
            $byte_var_type = @unpack("C", $binstring[$pos])[1];
            $pos += 1;


            // $cur_type = strtotime(base_convert($byte_var_type, 10, 16));

            switch ($byte_var_type) {
                case self::BINN_TRUE:
                    $this->_add_val(self::BINN_BOOL, true);
                    break;
                    
                case self::BINN_FALSE:
                    $this->_add_val(self::BINN_BOOL, false);
                    break;
                    
                case self::BINN_UINT64:
                    $this->_add_val(self::BINN_UINT64, unpack("J", substr($binstring, $pos, 8))[1]);
                    $pos += 8;
                    break;

                case self::BINN_UINT32:
                    $this->_add_val(self::BINN_UINT32, unpack("N", substr($binstring, $pos, 4))[1]);
                    $pos += 4;
                    break;
                    
                case self::BINN_UINT16:
                    $this->_add_val(self::BINN_UINT16, unpack("n", substr($binstring, $pos, 2))[1]);
                    $pos += 2;
                    break;
                    
                case self::BINN_UINT8:
                    $this->_add_val(self::BINN_UINT8, unpack("C", substr($binstring, $pos, 1))[1]);
                    $pos += 1;
                    break;

                case self::BINN_INT8:
                    $this->_add_val(self::BINN_INT8, unpack("c", substr($binstring, $pos, 1))[1]);
                    $pos += 1;
                    break;

                case self::BINN_INT16:
                    $this->_add_val(self::BINN_INT16, unpack("s", strrev(substr($binstring, $pos, 2)))[1]);
                    $pos += 2;
                    break;

                case self::BINN_INT32:
                    $this->_add_val(self::BINN_INT16, unpack("i", strrev(substr($binstring, $pos, 4)))[1]);
                    $pos += 4;
                    break;

                case self::BINN_INT64:
                    $this->_add_val(self::BINN_INT16, unpack("q", strrev(substr($binstring, $pos, 8)))[1]);
                    $pos += 8;
                    break;

                case self::BINN_STRING:
                    $string_size = unpack("C", $binstring[$pos])[1];

                    // Size
                    if ($string_size & 1 << 7) {
                        $string_size = unpack("N", substr($binstring, $pos, 4))[1];
                        $string_size = ($string_size &~ (1 << 31)); // Cut bit
                        $pos += 4;
                    } else {
                        $pos += 1;
                     }

                    $this->_add_val(self::BINN_STRING, unpack("a*", substr($binstring, $pos, $string_size))[1]);
                    $pos += $string_size;
                    $pos += 1; // Null byte
                    break;
                
                case self::BINN_LIST:
                    $list_size = unpack("C", $binstring[$pos])[1];
                    
                    // Size
                    if ($list_size & 1 << 7) {
                        $list_size = unpack("N", substr($binstring, $pos, 4))[1];
                        $list_size = ($list_size &~ (1 << 31)); // Cut bit
                    }

                    $substring = substr($binstring, $pos-1, $list_size);
                    $this->_add_val(self::BINN_LIST, new Binn($substring));

                    $pos += ($list_size-1);

                    break;
                    
                default:
                    $stop_while = true;
                    break;
            }

        }
    }
}