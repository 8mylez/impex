<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Util\Type;

abstract class ChunkStrategy
{
    abstract public function chunk(array $data, ConversionContext $context): array;

    public function merge(array $arrays, ConversionContext $context): array
    {
        $this->validateArrays($arrays, $context);

        return array_merge(...array_values($arrays));
    }

    protected function validateArrays(array $arrays, ConversionContext $context): void
    {
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($arrays as $key => $a) {
            if (!Type::is($a, Type::ARRAY)) {
                $subContext = $context->subContext(new Path([$key]));
                $exceptions->add(InvalidTypeException::invalidType(Type::ARRAY, $a, $subContext));
            }
        }

        $exceptions->throw();
    }
}
