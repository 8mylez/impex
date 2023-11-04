<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\ArrayUtil;

class ArrayEnsureStrategy extends ArrayConversionStrategy
{
    public function __construct(private bool $encaseArrays = false)
    {
    }

    public function convert(mixed $value, ConversionContext $context): array
    {
        if (!is_array($value)) {
            $value = ArrayUtil::ensure($value);
        } elseif ($this->encaseArrays === true) {
            $value = [$value];
        }

        return $value;
    }
}
