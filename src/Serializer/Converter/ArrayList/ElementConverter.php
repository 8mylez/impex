<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionInterface;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Util\Type;

class ElementConverter extends BidirectionalConverter
{
    public function __construct(
        private AttributeConverter $converter,
        string ...$flags
    ) {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $data, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        $data = $this->ensureType($data, Type::ARRAY, $context);

        $converted = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($data as $key => $value) {
            try {
                $converted[$key] = $this->converter->normalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionExceptionInterface $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $converted;
    }

    public function denormalize(mixed $data, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        $data = $this->ensureType($data, Type::ARRAY, $context);

        $converted = [];
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($data as $key => $value) {
            try {
                $converted[$key] = $this->converter->denormalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionExceptionInterface $e) {
                $exceptions->add($e);
            }
        }

        $exceptions->throw();

        return $converted;
    }
}
