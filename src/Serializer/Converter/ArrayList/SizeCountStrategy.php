<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class SizeCountStrategy extends CountStrategy
{
    public function count(array $data, ConversionContext $context): int
    {
        return count($data);
    }
}
