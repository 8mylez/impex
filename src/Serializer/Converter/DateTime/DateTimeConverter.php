<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

class DateTimeConverter extends BidirectionalConverter
{
    /**
     * @var string
     */
    private $format;

    /**
     * @var DateParser
     */
    private $parser;

    public function __construct(string $format, string ...$flags)
    {
        $this->format = $format;
        $this->parser = new DateParser($format, ...$flags);

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return array_unique(array_merge(
            [self::SKIP_NULL],
            DateParser::getAvailableFlags()
        ));
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->validateType($value, \DateTimeInterface::class, $path, $object->toArray());

        return $value->format($this->format);
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $data)
    {
        return $this->parser->convert($value, $object, $path, $attributeName, $data);
    }
}
