<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\ContextProviderInterface;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class NormalizerConverter extends BidirectionalConverter
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private DenormalizerInterface $denormalizer,
        private string $type,
        private ?string $format = null,
        private ?ContextProviderInterface $contextProvider = null
    ) {
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

        try {
            return $this->normalizer->normalize($value, $this->format, $context);
        } catch (CircularReferenceException|ExtraAttributesException|NotNormalizableValueException $e) {
            throw new AttributeConversionException($path, $object->toArray(), $e->getMessage());
        }
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $context = $this->contextProvider ? $this->contextProvider->getContext() : [];

        try {
            return $this->denormalizer->denormalize($value, $this->type, $this->format, $context);
        } catch (CircularReferenceException|ExtraAttributesException|NotNormalizableValueException $e) {
            throw new AttributeConversionException($path, $object->toArray(), $e->getMessage());
        }
    }
}
