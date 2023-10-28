<?php

namespace Dustin\ImpEx\Serializer\Converter\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\PropertyAccessor;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;

class Collector extends UnidirectionalConverter
{
    public function __construct(private array $paths, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        $result = [];

        foreach ($this->paths as $path) {
            try {
                $collected = PropertyAccessor::collect($path, $value, ...$this->flags);
            } catch (NotAccessableException|OperationNotSupportedException|PropertyNotFoundException $exception) {
                throw AttributeConversionException::fromErrorCode($context->getPath(), $context->getRootData(), $exception);
            }

            $result = array_merge_recursive($result, $collected);
        }

        return $result;
    }
}
