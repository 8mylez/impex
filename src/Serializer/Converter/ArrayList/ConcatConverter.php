<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Util\Type;

class ConcatConverter extends BidirectionalConverter
{
    /**
     * @var string
     */
    private $separator;

    public function __construct(string $separator, string ...$flags)
    {
        $this->separator = $separator;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
            self::REVERSE,
        ];
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if ($this->hasFlag(self::REVERSE)) {
            if (!$this->hasFlag(self::STRICT)) {
                $this->validateStringConvertable($value, $path, $object->toArray());

                $value = (string) $value;
            }

            $this->validateType($value, Type::STRING, $path, $object->toArray());

            return $this->explode($value);
        }

        if (!$this->hasFlag(self::STRICT)) {
            $value = (array) $value;
        }

        $this->validateType($value, Type::ARRAY, $path, $object->toArray());
        $this->validateStrings($value, $path, $object->toArray());

        return $this->implode($value);
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if ($this->hasFlag(self::REVERSE)) {
            if (!$this->hasFlag(self::STRICT)) {
                $value = (array) $value;
            }

            $this->validateType($value, Type::ARRAY, $path, $normalizedData);
            $this->validateStrings($value, $path, $normalizedData);

            return $this->implode($value);
        }

        if (!$this->hasFlag(self::STRICT)) {
            $this->validateStringConvertable($value, $path, $normalizedData);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $path, $normalizedData);

        return $this->explode($value);
    }

    public function explode(string $value): array
    {
        return explode($this->separator, $value);
    }

    public function implode(array $value): string
    {
        return implode($this->separator, $value);
    }

    private function validateStrings(array $strings, string $path, array $data): void
    {
        $exceptions = [];

        foreach ($strings as $key => $v) {
            $subPath = $path.'/'.$key;
            try {
                $this->validateStringConvertable($v, $subPath, $data);
            } catch (AttributeConversionException $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($path, $data, ...$exceptions);
        }
    }
}
