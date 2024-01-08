<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class SerializationConversionException extends AttributeConversionException
{
    public const CIRCULAR_REFERENCE_ERROR = 'IMPEX_CONVERSION__CIRCULAR_REFERENCE_ERROR';

    public const EXTRA_ATTRIBUTES_ERROR = 'IMPEX_CONVERSION__EXTRA_ATTRIBUTES_ERROR';

    public const NOT_NORMALIZABLE_VALUE_ERROR = 'IMPEX_CONVERSION___NOT_NORMALIZABLE_VALUE_ERROR';

    public static function circularReference(ConversionContext $context): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            'Circular reference was detected',
            [],
            self::CIRCULAR_REFERENCE_ERROR
        );
    }

    public static function extraAttributes(array $extraAttributes, ConversionContext $context): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            'The attributes {{ extraAttributes }} are not allowed.',
            ['extraAttributes' => implode(', ', $extraAttributes)],
            self::EXTRA_ATTRIBUTES_ERROR
        );
    }

    public static function notNormalizableValue(string $message, ConversionContext $context): self
    {
        return new self(
            $context->getPath(), $context->getRootData(),
            $message,
            [],
            self::NOT_NORMALIZABLE_VALUE_ERROR
        );
    }
}
