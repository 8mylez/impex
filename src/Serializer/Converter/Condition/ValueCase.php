<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;

class ValueCase extends Condition
{
    /**
     * @var array
     */
    private $values = [];

    /**
     * @var AttributeConverter
     */
    private $converter;

    /**
     * @var bool
     */
    private $strict;

    public function __construct(array $values, AttributeConverter $converter, bool $strict = false)
    {
        $this->values = $values;
        $this->converter = $converter;
        $this->strict = $strict;
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
