<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\ContextProviderInterface;
use Dustin\ImpEx\Serializer\Exception\SerializationConversionException;
use Dustin\ImpEx\Serializer\Normalizer\EncapsulationNormalizer;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerConverter extends BidirectionalConverter
{
    public function __construct(
        private SerializerInterface $serializer,
        private string $format,
        private string $type,
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
        $context[EncapsulationNormalizer::CONVERSION_ROOT_PATH] = $path;

        try {
            return $this->serializer->serialize($value, $this->format, $context);
        } catch (CircularReferenceException $exception) {
            throw SerializationConversionException::circularReference($path, $object->toArray());
        } catch (ExtraAttributesException $exception) {
            throw SerializationConversionException::extraAttributes($path, $object->toArray(), $exception->getExtraAttributes());
        } catch (NotNormalizableValueException $exception) {
            throw SerializationConversionException::notNormalizableValue($path, $object->toArray(), $exception->getMessage());
        }
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $context = $this->contextProvider ? $this->contextProvider->getContext() : [];
        $context[EncapsulationNormalizer::CONVERSION_ROOT_PATH] = $path;

        try {
            return $this->serializer->deserialize($value, $this->type, $this->format, $context);
        } catch (CircularReferenceException $exception) {
            throw SerializationConversionException::circularReference($path, $normalizedData);
        } catch (ExtraAttributesException $exception) {
            throw SerializationConversionException::extraAttributes($path, $normalizedData, $exception->getExtraAttributes());
        } catch (NotNormalizableValueException $exception) {
            throw SerializationConversionException::notNormalizableValue($path, $normalizedData, $exception->getMessage());
        }
    }
}
