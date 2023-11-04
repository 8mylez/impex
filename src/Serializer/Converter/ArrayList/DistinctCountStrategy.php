<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\TypeConversionException;
use Dustin\ImpEx\Util\Type;

class DistinctCountStrategy extends CountStrategy
{
    public function count(array $data, ConversionContext $context): array
    {
        $exceptions = [];

        foreach ($data as $key => $value) {
            $subContext = $context->subContext(new Path([$key]));

            if (!Type::isStringConvertable(Type::getType($value))) {
                $exceptions[] = TypeConversionException::string($subContext->getPath(), $subContext->getRootData(), $value);
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($context->getPath(), $context->getRootData(), ...$exceptions);
        }

        return array_count_values($data);
    }
}
