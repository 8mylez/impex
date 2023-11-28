<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class SubString extends UnidirectionalConverter
{
    public function __construct(
        private int|StringPosition $offset,
        private null|int|StringPosition $length = null,
        string ...$flags
    ) {
        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->ensureType($value, Type::STRING, $context);

        $offset = $this->offset;

        if ($offset instanceof StringPosition) {
            $offset = $offset->convert($value, $context);

            if ($offset === null) {
                return $value;
            }
        }

        $length = $this->length;

        if ($length instanceof StringPosition) {
            $length = $length->convert($value, $context);
        }

        return mb_substr($value, $offset, $length);
    }
}
