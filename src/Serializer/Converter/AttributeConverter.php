<?php

namespace Dustin\ImpEx\Serializer\Converter;

/**
 * Converter base class to convert an attribute value.
 *
 * Attribute values can be converted in both directions (normalization and denormalization).
 * Flags can be set to change conversion behavior and influence error handling.
 */
abstract class AttributeConverter
{
    use ConversionTrait;

    public const SKIP_NULL = 'skip_null';

    public const STRICT = 'strict';

    public const REVERSE = 'reverse';

    public const REINDEX = 'reindex';

    /**
     * @param string ...$flags An optional list of flags to affect conversion behavior
     */
    public function __construct(string ...$flags)
    {
        $this->setFlags(...$flags);
    }

    abstract public function normalize(mixed $value, ConversionContext $context): mixed;

    abstract public function denormalize(mixed $value, ConversionContext $context): mixed;
}
