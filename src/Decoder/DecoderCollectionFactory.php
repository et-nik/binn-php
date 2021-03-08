<?php

namespace Knik\Binn\Decoder;

use Knik\Binn\Binn;
use Knik\Binn\Decoder\Containers\BinnListDecoder;
use Knik\Binn\Decoder\Containers\BinnMapDecoder;
use Knik\Binn\Decoder\Containers\BinnObjectDecoder;

class DecoderCollectionFactory
{
    public function getCollection(): DecoderCollection
    {
        $decoderCollection = new DecoderCollection();

        $simpleStorageValueDecoder = new SimpleStorageValueDecoder();

        $decoderCollection->add(Binn::BINN_TRUE, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_FALSE, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_UINT8, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_INT8, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_UINT16, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_INT16, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_UINT32, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_INT32, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_UINT64, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_INT64, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_STRING, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_FLOAT32, $simpleStorageValueDecoder);
        $decoderCollection->add(Binn::BINN_FLOAT64, $simpleStorageValueDecoder);

        $decoderCollection->add(Binn::BINN_LIST, new BinnListDecoder($decoderCollection));
        $decoderCollection->add(Binn::BINN_MAP, new BinnMapDecoder($decoderCollection));
        $decoderCollection->add(Binn::BINN_OBJECT, new BinnObjectDecoder($decoderCollection));

        return $decoderCollection;
    }
}
