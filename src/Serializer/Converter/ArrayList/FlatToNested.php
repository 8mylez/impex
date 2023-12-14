<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class FlatToNested extends BidirectionalConverter
{
    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::ARRAY, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return ArrayUtil::nestedToFlat($value);
        }

        return ArrayUtil::flatToNested($value);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::ARRAY, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return ArrayUtil::flatToNested($value);
        }

        return ArrayUtil::nestedToFlat($value);
    }
}
