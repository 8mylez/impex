<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

class DateTimeConverter extends BidirectionalConverter
{
    /**
     * @var DateParser
     */
    private $parser;

    public function __construct(private string $format, string ...$flags)
    {
        $this->parser = new DateParser($format, ...$flags);

        parent::__construct(...$flags);
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
