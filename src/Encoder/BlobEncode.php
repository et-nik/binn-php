<?php

namespace Knik\Binn\Encoder;

use Knik\Binn\Binn;
use Knik\Binn\Contracts\BinnValueEncoder;
use Knik\Binn\Exceptions\BinnException;

class BlobEncode implements BinnValueEncoder
{
    public const TYPE = Binn::BINN_STORAGE_BLOB;

    public function encode($value): string
    {
        if (!$this->supportsEncoding($value)) {
            throw new BinnException('Invalid value. Resource expected');
        }

        $contents = '';

        while (!feof($value)) {
            $contents .= fread($value, 1024);
        }

        $encodedType  = Packer::packUint8(self::TYPE);
        $encodedSize = Packer::packSize(strlen($encodedType) + strlen($contents), true);

        return $encodedType . $encodedSize . $contents;
    }

    public function supportsEncoding($value): bool
    {
        return is_resource($value);
    }

}
