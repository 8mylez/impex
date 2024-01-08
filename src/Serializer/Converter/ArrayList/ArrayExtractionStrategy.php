<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

abstract class ArrayExtractionStrategy
{
    abstract public function extract(array $data, ConversionContext $context): mixed;
}
