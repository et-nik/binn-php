<?php

namespace Knik\Binn\Decoder\Containers;

use Knik\Binn\Binn;
use Knik\Binn\Contracts\BinnValueDecoder;
use Knik\Binn\Decoder\Decoder;
use Knik\Binn\Decoder\DecoderCollection;
use Knik\Binn\Decoder\Unpacker;

class BinnMapDecoder extends Decoder implements BinnValueDecoder
{
    /** @var DecoderCollection */
    private $decoders;

    public function __construct(DecoderCollection $decoders)
    {
        $this->decoders = $decoders;
    }

    public function decode(string $bytes)
    {
        $readPosition = 1;
        $totalSize = Unpacker::unpackSize8($bytes[$readPosition]);

        if ($totalSize > Binn::BINN_MAX_ONE_BYTE_SIZE) {
            $totalSize = Unpacker::unpackSize32(substr($bytes, $readPosition, 4));
            $readPosition += 4;
        } else {
            $readPosition++;
        }

        $totalItems = Unpacker::unpackSize8($bytes[$readPosition]);

        if ($totalItems > Binn::BINN_MAX_ONE_BYTE_SIZE) {
            $totalItems = Unpacker::unpackSize32(substr($bytes, $readPosition, 4));
            $readPosition += 4;
        } else {
            $readPosition++;
        }

        $readedItems = 0;

        $result = [];

        while ($readedItems < $totalItems && $readPosition < $totalSize) {
            $keyValue = Unpacker::unpackInt32(substr($bytes, $readPosition, 4));
            $readPosition += 4;

            $readSize = $this->readSizeWithType(substr($bytes, $readPosition, 5));
            $innerStorage = substr($bytes, $readPosition, $readSize);

            /** @var BinnValueDecoder $decoder */
            foreach ($this->decoders->getAll() as $decoder) {
                if ($decoder->supportsDecoding($innerStorage)) {
                    $result[$keyValue] = $decoder->decode($innerStorage);
                    break;
                }
            }

            $readPosition += $readSize;
            $readedItems++;
        }

        return $result;
    }

    public function supportsDecoding(string $bytes): bool
    {
        $type = $this->detectType($bytes);

        return $type === Binn::BINN_MAP;
    }
}
