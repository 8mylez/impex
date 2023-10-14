<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class ValueCase extends Condition
{
    public function __construct(private array $values, private AttributeConverter $converter, private bool $strict = false)
    {
    }

    public function isFullfilled(mixed $value, ConversionContext $context): bool
    {
        return \in_array($value, $this->values, $this->strict);
    }

    public function getConverter(): AttributeConverter
    {
        return $this->converter;
    }
}
