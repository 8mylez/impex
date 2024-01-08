<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionInterface;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;

class AttributeConversions extends BidirectionalConverter
{
    private $converters = [];

    public function __construct(array $converters, string ...$flags)
    {
        foreach (array_values($converters) as $index => $converter) {
            if (!$converter instanceof AttributeConverter) {
                throw new \InvalidArgumentException(sprintf('Element #%s must be of type %s. %s given.', $index, AttributeConverter::class, get_debug_type($converter)));
            }
        }

        $this->converters = $converters;

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $result = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($this->converters as $key => $converter) {
            try {
                $result[$key] = $converter->normalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionExceptionInterface $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $result;
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $result = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($this->converters as $key => $converter) {
            try {
                $result[$key] = $converter->denormalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionExceptionInterface $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $result;
    }
}
