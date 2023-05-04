<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\ContextProviderInterface;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerConverter extends BidirectionalConverter
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $type;

    /**
     * @var ContextProviderInterface|null
     */
    private $contextProvider;

    public function __construct(
        SerializerInterface $serializer,
        string $format,
        string $type,
        ?ContextProviderInterface $contextProvider = null
    ) {
        $this->serializer = $serializer;
        $this->format = $format;
        $this->type = $type;
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

        try {
            return $this->serializer->serialize($value, $this->format, $context);
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
            return $this->serializer->deserialize($value, $this->type, $this->format, $context);
        } catch (ExtraAttributesException|NotNormalizableValueException $e) {
            throw new AttributeConversionException($path, $normalizedData, $e->getMessage());
        }
    }
}
