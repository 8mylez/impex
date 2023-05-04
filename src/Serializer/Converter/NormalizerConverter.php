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
    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string|null
     */
    private $format = null;

    /**
     * @var ContextProviderInterface|null
     */
    private $contextProvider;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        string $type,
        ?string $format = null,
        ?ContextProviderInterface $contextProvider = null
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
        $this->type = $type;
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
