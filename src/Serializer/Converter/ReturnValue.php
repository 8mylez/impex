<?php

namespace Dustin\ImpEx\Serializer\Converter;

class ReturnValue extends UnidirectionalConverter
{
    public function __construct()
    {
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        return $value;
    }
}
