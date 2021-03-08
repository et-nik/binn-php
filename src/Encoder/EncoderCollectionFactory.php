<?php

namespace Knik\Binn\Encoder;

use Knik\Binn\Binn;
use Knik\Binn\Encoder\Containers\BinnListEncoder;
use Knik\Binn\Encoder\Containers\BinnMapEncoder;
use Knik\Binn\Encoder\Containers\BinnObjectEncoder;

class EncoderCollectionFactory
{
    public function getCollection(): EncoderCollection
    {
        $encoderCollection = new EncoderCollection();

        $simpleTypeEncoder = new SimpleTypeValueEncoder();

        $encoderCollection->add(Binn::BINN_TRUE, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_FALSE, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_UINT8, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_INT8, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_UINT16, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_INT16, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_UINT32, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_INT32, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_UINT64, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_INT64, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_STRING, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_FLOAT32, $simpleTypeEncoder);
        $encoderCollection->add(Binn::BINN_FLOAT64, $simpleTypeEncoder);

        $encoderCollection->add(Binn::BINN_STORAGE_BLOB, new BlobEncode());

        $encoderCollection->add(Binn::BINN_LIST, new BinnListEncoder($encoderCollection));
        $encoderCollection->add(Binn::BINN_MAP, new BinnMapEncoder($encoderCollection));
        $encoderCollection->add(Binn::BINN_OBJECT, new BinnObjectEncoder($encoderCollection));

        return $encoderCollection;
    }
}
