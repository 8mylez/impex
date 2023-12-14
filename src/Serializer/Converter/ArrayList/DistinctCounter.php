<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\TypeConversionException;
use Dustin\ImpEx\Util\Type;

class DistinctCounter extends UnidirectionalConverter
{
    public function convert(mixed $data, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        $this->ensureType($data, Type::ARRAY, $context);

        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($data as $key => $value) {
            if (!Type::isStringConvertable(Type::getType($value))) {
                $subContext = $context->subContext(new Path([$key]));
                $exceptions->add(TypeConversionException::string($value, $subContext));
            }
        }

        $exceptions->throw();

        return array_count_values($data);
    }
}
