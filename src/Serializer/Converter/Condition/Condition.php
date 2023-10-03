<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

abstract class Condition
{
    abstract public function isFullfilled(mixed $value, ConversionContext $context): bool;
}
