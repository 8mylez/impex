<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\ContextProviderInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

class EncoderConverter extends BidirectionalConverter
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var string
     */
    private $format;

    /**
     * @var ContextProviderInterface|null
     */
    private $contextProvider;

    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $decoder,
        string $format,
        ?ContextProviderInterface $contextProvider = null
    ) {
        $this->encoder = $encoder;
        $this->decoder = $decoder;
        $this->format = $format;
        $this->contextProvider = $contextProvider;
    }

    public static function getAvailableFlags(): array
    {
        return [self::SKIP_NULL];
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $context = $this->contextProvider ? $this->contextProvider->getContext() : [];

        return $this->encoder->encode($value, $this->format, $context);
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $context = $this->contextProvider ? $this->contextProvider->getContext() : [];

        return $this->decoder->decode($value, $this->format, $context);
    }
}
