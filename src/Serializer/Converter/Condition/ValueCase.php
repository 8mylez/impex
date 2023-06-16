<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;

class ValueCase extends Condition
{
    public function __construct(private array $values, private AttributeConverter $converter, private bool $strict = false)
    {
    }

    public function isFullfilled($value, EncapsulationInterface $object, string $path, string $attributeName): bool
    {
        return \in_array($value, $this->values, $this->strict);
    }

    public function getConverter(): AttributeConverter
    {
        return $this->converter;
    }
}
