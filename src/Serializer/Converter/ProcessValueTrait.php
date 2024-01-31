<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;

trait ProcessValueTrait
{
    protected function processValue(mixed $value, ConversionContext $context): mixed
    {
        if ($value instanceof AccessOperation) {
            $input = $context->getNormalizedData() ?? $context->getObject();

            try {
                return $value->execute($input);
            } catch (InvalidDataException|NotAccessableException|OperationNotSupportedException|PropertyNotFoundException $exception) {
                throw AttributeConversionException::fromErrorCode($exception, $context);
            }
        }

        if ($value instanceof AttributeConverter) {
            if ($context->getDirection() === ConversionContext::NORMALIZATION) {
                return $value->normalize($context->getObject(), $context);
            }

            return $value->denormalize($context->getNormalizedData(), $context);
        }

        if (is_callable($value)) {
            try {
                return $value($context->getNormalizedData() ?? $context->getObject(), $context);
            } catch (\Throwable $error) {
                throw AttributeConversionException::fromException($error, $context);
            }
        }

        return $value;
    }
}
