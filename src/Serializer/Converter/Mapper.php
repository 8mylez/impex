<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\Encapsulation;
use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Util\Type;

class Mapper extends BidirectionalConverter
{
    public function __construct(
        private EncapsulationInterface $normalizationMapping,
        private ?EncapsulationInterface $denormalizationMapping = null,
        string ...$flags
    ) {
        parent::__construct(...$flags);

        if ($denormalizationMapping === null) {
            $this->denormalizationMapping = new Encapsulation(array_flip($normalizationMapping->toArray()));
        }
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $this->validateStringConvertable($value, $context);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $context);

        if (!$this->normalizationMapping->has($value)) {
            return $value;
        }

        return $this->normalizationMapping->get($value);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $this->validateStringConvertable($value, $context);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $context);

        if (!$this->denormalizationMapping->has($value)) {
            return $value;
        }

        return $this->denormalizationMapping->get($value);
    }
}
