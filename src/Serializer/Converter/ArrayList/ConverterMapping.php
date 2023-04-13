<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class ConverterMapping extends BidirectionalConverter
{
    private $converters = [];

    public function __construct(array $converters, string ...$flags)
    {
        foreach ($converters as $name => $converter) {
            $this->setConverter($name, $converter);
        }

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
        ];
    }

    public function setConverter(string $field, ?AttributeConverter $converter)
    {
        $this->converters[$field] = $converter;
    }

    public function getConverter(string $field): ?AttributeConverter
    {
        return $this->converters[$field] ?? null;
    }

    public function normalize($data, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $data = (array) $data;
        }

        $this->validateType($data, Type::ARRAY, $path, $object->toArray());

        $converted = [];

        foreach ($data as $name => $value) {
            $converter = $this->getConverter($name);
            $converted[$name] = $converter !== null ? $converter->normalize($value, $object, $path.'/'.$name, $attributeName) : $value;
        }

        return $converted;
    }

    public function denormalize($data, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $data = (array) $data;
        }

        $this->validateType($data, Type::ARRAY, $path, $normalizedData);

        $converted = [];

        foreach ($data as $name => $value) {
            $converter = $this->getConverter($name);
            $converted[$name] = $converter !== null ? $converter->denormalize($value, $object, $path.'/'.$name, $attributeName, $normalizedData) : $value;
        }

        return $converted;
    }
}
