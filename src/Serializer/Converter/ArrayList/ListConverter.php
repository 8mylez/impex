<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Util\Type;

class ListConverter extends BidirectionalConverter
{
    /**
     * @var AttributeConverter
     */
    private $converter;

    public function __construct(
        AttributeConverter $converter,
        string ...$flags
    ) {
        $this->converter = $converter;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
        ];
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
        $exceptions = [];

        foreach ($data as $key => $value) {
            $subPath = $path.'/'.$key;

            try {
                $converted[$key] = $this->converter->normalize($value, $object, $subPath, $attributeName);
            } catch (AttributeConversionException $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($path, $object->toArray(), ...$exceptions);
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
        $exceptions = [];

        foreach ($data as $key => $value) {
            $subPath = $path.'/'.$key;

            try {
                $converted[$key] = $this->converter->denormalize($value, $object, $subPath, $attributeName, $normalizedData);
            } catch (AttributeConversionException $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($path, $normalizedData, ...$exceptions);
        }

        return $converted;
    }
}
