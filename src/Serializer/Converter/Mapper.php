<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\Util\Type;

class Mapper extends BidirectionalConverter
{
    public function __construct(
        private array $normalizationMapping,
        private ?array $denormalizationMapping = null,
        string ...$flags
    ) {
        parent::__construct(...$flags);

        if ($denormalizationMapping === null) {
            $this->denormalizationMapping = array_flip($normalizationMapping);
        }
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);

        if (!array_key_exists($value, $this->normalizationMapping)) {
            return $value;
        }

        return $this->normalizationMapping[$value];
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);

        if (!array_key_exists($value, $this->denormalizationMapping)) {
            return $value;
        }

        return $this->denormalizationMapping[$value];
    }
}
