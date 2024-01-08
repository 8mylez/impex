<?php

namespace Dustin\ImpEx\Serializer\Converter\DateTime;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

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

    public function normalize(mixed $value, ConversionContext $context): string|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->validateType($value, \DateTimeInterface::class, $context);

        return $value->format($this->format);
    }

    public function denormalize(mixed $value, ConversionContext $context): \DateTimeInterface|null
    {
        return $this->parser->convert($value, $context);
    }
}
