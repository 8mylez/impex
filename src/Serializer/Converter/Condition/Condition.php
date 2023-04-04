<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;

abstract class Condition
{
    abstract public function isFullfilled($value, EncapsulationInterface $object, string $path, string $attributeName): bool;
}
