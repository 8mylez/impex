<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Util\Type;

class ConverterMapping extends BidirectionalConverter
{
    public const ITERATE_CONVERTERS = 'iterate_converters';

    private $converters = [];

    public function __construct(array $converters, string ...$flags)
    {
        foreach ($converters as $name => $converter) {
            $this->setConverter($name, $converter);
        }

        parent::__construct(...$flags);
    }

    public function setConverter(int|string $field, ?AttributeConverter $converter)
    {
        $this->converters[$field] = $converter;
    }

    public function getConverter(int|string $field): ?AttributeConverter
    {
        return $this->converters[$field] ?? null;
    }

    public function normalize(mixed $data, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if ($this->hasFlags(self::ITERATE_CONVERTERS)) {
            return $this->normalizeWithConverters($data, $context);
        }

        $this->ensureType($data, Type::ARRAY, $context);

        return $this->normalizeWithValue($data, $context);
    }

    public function denormalize(mixed $data, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if ($this->hasFlags(self::ITERATE_CONVERTERS)) {
            return $this->denormalizeWithConverters($data, $context);
        }

        $this->ensureType($data, Type::ARRAY, $context);

        return $this->denormalizeWithValue($data, $context);
    }

    protected function normalizeWithConverters(mixed $value, ConversionContext $context): array
    {
        $converted = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($this->converters as $key => $converter) {
            try {
                $converted[$key] = $converter->normalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionException $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $converted;
    }

    protected function normalizeWithValue(array $data, ConversionContext $context): array
    {
        $converted = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($data as $key => $value) {
            $converter = $this->getConverter($key);

            try {
                $converted[$key] = $converter !== null ? $converter->normalize($value, $context->subContext(new Path([$key]))) : $value;
            } catch (AttributeConversionException $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $converted;
    }

    protected function denormalizeWithConverters(mixed $value, ConversionContext $context): array
    {
        $converted = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($this->converters as $key => $converter) {
            try {
                $converted[$key] = $converter->denormalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionException $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $converted;
    }

    protected function denormalizeWithValue(array $data, ConversionContext $context): array
    {
        $converted = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($data as $key => $value) {
            $converter = $this->getConverter($key);

            try {
                $converted[$key] = $converter !== null ? $converter->denormalize($value, $context->subContext(new Path([$key]))) : $value;
            } catch (AttributeConversionException $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $converted;
    }
}
