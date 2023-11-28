<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class Chunker extends BidirectionalConverter
{
    public function __construct(private ChunkStrategy $strategy, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return $value;
        }

        $this->ensureType($value, Type::ARRAY, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->strategy->merge($value, $context);
        }

        return $this->strategy->chunk($value, $context);
    }

    public function denormalize(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->ensureType($value, Type::ARRAY, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->strategy->chunk($value, $context);
        }

        return $this->strategy->merge($value, $context);
    }
}
