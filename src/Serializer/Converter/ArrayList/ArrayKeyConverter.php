<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

class ArrayKeyConverter extends BidirectionalConverter
{
    public function __construct(private NameConverterInterface $nameConverter, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $value = ArrayUtil::ensure($value);
        }

        $this->validateType($value, Type::ARRAY, $context);

        $converted = [];

        foreach ($value as $key => $v) {
            $converted[$this->nameConverter->normalize($key)] = $v;
        }

        return $converted;
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $value = ArrayUtil::ensure($value);
        }

        $this->validateType($value, Type::ARRAY, $context);

        $converted = [];

        foreach ($value as $key => $v) {
            $converted[$this->nameConverter->denormalize($key)] = $v;
        }

        return $converted;
    }
}
