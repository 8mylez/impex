<?php

namespace Dustin\ImpEx\Serializer\Converter\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\UnknownErrorException;

class AccessOperationConverter extends UnidirectionalConverter
{
    public function __construct(private AccessOperation $operation, private mixed $writeValue = null)
    {
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        $writeValue = $this->writeValue;

        if (is_callable($writeValue)) {
            $writeValue = $writeValue($value, $context);
        }

        try {
            $this->operation->execute($value, $writeValue);
        } catch (InvalidDataException|NotAccessableException|OperationNotSupportedException|PropertyNotFoundException $exception) {
            throw new UnknownErrorException($context->getPath(), $context->getRootData(), $exception->getMessage(), $exception->getParameters(), $exception->getErrorCode());
        }
    }
}
