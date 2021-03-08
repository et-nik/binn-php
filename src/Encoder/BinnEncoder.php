<?php

namespace Knik\Binn\Encoder;

use Knik\Binn\Decoder\BinnDecode;
use Knik\Binn\Decoder\DecoderCollectionFactory;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class BinnEncoder implements EncoderInterface, DecoderInterface
{
    public const FORMAT = 'binn';

    protected $encodingImpl;
    protected $decodingImpl;

    public function __construct(BinnEncode $encodingImpl = null, BinnDecode $decodingImpl = null)
    {
        $this->encodingImpl = $encodingImpl ?: new BinnEncode(
            (new EncoderCollectionFactory())->getCollection()
        );

        $this->decodingImpl = $decodingImpl ?: new BinnDecode(
            (new DecoderCollectionFactory())->getCollection()
        );
    }

    public function decode(string $data, string $format, array $context = [])
    {
        return $this->decodingImpl->decode($data, $format, $context);
    }

    public function encode($data, string $format, array $context = [])
    {
        return $this->encodingImpl->encode($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding(string $format)
    {
        return self::FORMAT === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding(string $format)
    {
        return self::FORMAT === $format;
    }
}
