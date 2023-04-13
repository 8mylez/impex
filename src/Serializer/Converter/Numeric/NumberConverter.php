<?php

namespace Dustin\ImpEx\Serializer\Converter\Numeric;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

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

    public static function getAvailableFlags(): array
    {
        return array_unique(array_merge(
            [self::REVERSE],
            Parser::getAvailableFlags(),
            Formatter::getAvailableFlags()
        ));
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        return !$this->hasFlag(self::REVERSE) ?
            $this->formatter->convert($value, $object, $path, $attributeName) :
            $this->parser->convert($value, $object, $path, $attributeName);
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $data)
    {
        return !$this->hasFlag(self::REVERSE) ?
            $this->parser->convert($value, $object, $path, $attributeName, $data) :
            $this->formatter->convert($value, $object, $path, $attributeName, $data);
    }
}
