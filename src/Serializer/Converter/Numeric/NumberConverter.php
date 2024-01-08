<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class NumberConverter extends BidirectionalConverter
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Formatter
     */
    private $formatter;

    public function __construct(
        string $decimalSeparator = '.',
        string $thousandsSeparator = ',',
        int $decimals = 3,
        string ...$flags
    ) {
        $this->parser = new Parser($decimalSeparator, $thousandsSeparator, ...$flags);
        $this->formatter = new Formatter($decimalSeparator, $thousandsSeparator, $decimals, ...$flags);

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): string|null|int|float
    {
        return !$this->hasFlags(self::REVERSE) ?
            $this->formatter->convert($value, $context) :
            $this->parser->convert($value, $context);
    }

    public function denormalize(mixed $value, ConversionContext $context): string|null|int|float
    {
        return !$this->hasFlags(self::REVERSE) ?
            $this->parser->convert($value, $context) :
            $this->formatter->convert($value, $context);
    }
}
