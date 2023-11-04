<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

abstract class CountStrategy
{
    abstract public function count(array $data, ConversionContext $context): mixed;
}
