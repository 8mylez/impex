<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

abstract class ArrayConversionStrategy
{
    abstract public function convert(mixed $value, ConversionContext $context): array;
}
