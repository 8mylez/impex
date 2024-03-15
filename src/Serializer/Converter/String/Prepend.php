<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\ProcessValueTrait;
use Dustin\ImpEx\Util\Type;

class Prepend extends BidirectionalConverter
{
    use ProcessValueTrait;

    /**
     * @var string|AccessOperation|AttributeConverter|callable
     */
    private $prefix;

    public function __construct(string|AccessOperation|AttributeConverter|callable $prefix, string ...$flags)
    {
        $this->prefix = $prefix;

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);
        $prefix = $this->processValue($this->prefix, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->ltrim($value, $prefix);
        }

        return $this->prepend($value, $prefix);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);
        $prefix = $this->processValue($this->prefix, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->prepend($value, $prefix);
        }

        return $this->ltrim($value, $prefix);
    }

    protected function prepend(string $value, string $prefix): string
    {
        return $prefix.$value;
    }

    protected function ltrim(string $value, string $prefix): string
    {
        if (!str_starts_with($value, $prefix)) {
            return $value;
        }

        return substr($value, strlen($prefix));
    }
}
