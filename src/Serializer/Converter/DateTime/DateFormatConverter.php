<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

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

    public static function getAvailableFlags(): array
    {
        return DateParser::getAvailableFlags();
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        $date = $this->attributeParser->normalize($value, $object, $path, $attributeName);

        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->rawFormat);
        }

        return $date;
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $data)
    {
        $date = $this->rawParser->denormalize($value, $object, $path, $attributeName, $data);

        if ($date instanceof \DateTimeInterface) {
            return $date->format($this->attributeFormat);
        }

        return $date;
    }
}
