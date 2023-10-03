<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class EncoderConverter extends BidirectionalConverter
{
    public function __construct(
        private EncoderInterface $encoder,
        private DecoderInterface $decoder,
        private string $format,
        string ...$flags
    ) {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        return $this->encoder->encode($value, $this->format, $context->getNormalizationContext());
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        return $this->decoder->decode($value, $this->format, $context->getNormalizationContext());
    }
}
