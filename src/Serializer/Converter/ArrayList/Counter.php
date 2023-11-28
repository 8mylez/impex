<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Counter extends UnidirectionalConverter
{
    public function __construct(private CountStrategy $strategy, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->ensureType($value, Type::ARRAY, $context);

        return $this->strategy->count($value, $context);
    }
}
