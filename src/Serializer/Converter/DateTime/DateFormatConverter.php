<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class DateFormatConverter extends BidirectionalConverter
{
    /**
     * @var DateParser
     */
    private $attributeParser;

    /**
     * @var DateParser
     */
    private $rawParser;

    public function __construct(private string $attributeFormat, private string $rawFormat, string ...$flags)
    {
        $this->attributeParser = new DateParser($attributeFormat, ...$flags);
        $this->rawParser = new DateParser($rawFormat, ...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): string|null
    {
        $date = $this->attributeParser->normalize($value, $context);

        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->rawFormat);
        }

        return $date;
    }

    public function denormalize(mixed $value, ConversionContext $context): string|null
    {
        $date = $this->rawParser->denormalize($value, $context);

        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->attributeFormat);
        }

        return $date;
    }
}
