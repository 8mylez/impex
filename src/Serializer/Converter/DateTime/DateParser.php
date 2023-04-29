<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\DateConversionException;
use Dustin\ImpEx\Util\Type;

class DateParser extends UnidirectionalConverter
{
    /**
     * @var string|null
     */
    private $format;

    public function __construct(?string $format = null, string ...$flags)
    {
        $this->format = $format;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
        ];
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
            throw new DateConversionException($path, $data ?? $object->toArray(), sprintf("Could not create date from string '%s'.", $value));
        }

        return $date;
    }
}
