<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

abstract class ArrayKeyConversionStrategy
{
    abstract public function normalizeKeys(array $data, ConversionContext $context): array;

    abstract public function denormalizeKeys(array $data, ConversionContext $context): array;
}
