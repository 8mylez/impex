<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\DateConversionException;
use Dustin\ImpEx\Util\Type;

class DateParser extends UnidirectionalConverter
{
    public function __construct(private ?string $format = null, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function createDateTime(string $value): ?\DateTimeInterface
    {
        $date = $this->format !== null ? \date_create_from_format($this->format, $value) : \date_create($value);

        return $date !== false ? $date : null;
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $this->validateStringConvertable($value, $path, $data ?? $object->toArray());

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $path, $data ?? $object->toArray());

        $date = $this->createDateTime($value);

        if ($date === null) {
            throw new DateConversionException($path, $data ?? $object->toArray(), "Could not create date from string '{{ dateTime }}'.", ['dateTime' => $value]);
        }

        return $date;
    }
}
