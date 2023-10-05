<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\Serializer\Exception\SerializationConversionException;
use Dustin\ImpEx\Serializer\Normalizer\ConversionNormalizer;
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
        string ...$flags
    ) {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $normalizationContext = $context->getNormalizationContext();
        $normalizationContext[ConversionNormalizer::CONVERSION_CONTEXT] = $context;

        try {
            return $this->serializer->serialize($value, $this->format, $normalizationContext);
        } catch (CircularReferenceException $exception) {
            throw SerializationConversionException::circularReference($context->getPath(), $context->getRootData());
        } catch (ExtraAttributesException $exception) {
            throw SerializationConversionException::extraAttributes($context->getPath(), $context->getRootData(), $exception->getExtraAttributes());
        } catch (NotNormalizableValueException $exception) {
            throw SerializationConversionException::notNormalizableValue($context->getPath(), $context->getRootData(), $exception->getMessage());
        }
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $normalizationContext = $context->getNormalizationContext();
        $normalizationContext[ConversionNormalizer::CONVERSION_CONTEXT] = $context;

        try {
            return $this->serializer->deserialize($value, $this->type, $this->format, $normalizationContext);
        } catch (CircularReferenceException $exception) {
            throw SerializationConversionException::circularReference($context->getPath(), $context->getRootData());
        } catch (ExtraAttributesException $exception) {
            throw SerializationConversionException::extraAttributes($context->getPath(), $context->getRootData(), $exception->getExtraAttributes());
        } catch (NotNormalizableValueException $exception) {
            throw SerializationConversionException::notNormalizableValue($context->getPath(), $context->getRootData(), $exception->getMessage());
        }
    }
}
