<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
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

    public function convert(mixed $value, ConversionContext $context): \DateTimeInterface|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $this->validateStringConvertable($value, $context);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $context);

        $date = $this->createDateTime($value);

        if ($date === null) {
            throw new DateConversionException($context->getPath(), $context->getRootData(), "Could not create date from string '{{ dateTime }}'.", ['dateTime' => $value]);
        }

        return $date;
    }
}
