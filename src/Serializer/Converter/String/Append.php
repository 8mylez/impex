<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\ProcessValueTrait;
use Dustin\ImpEx\Util\Type;

class Append extends BidirectionalConverter
{
    use ProcessValueTrait;

    /**
     * @var string|AccessOperation|AttributeConverter|callable
     */
    private $suffix;

    public function __construct(string|AccessOperation|AttributeConverter|callable $suffix, string ...$flags)
    {
        $this->suffix = $suffix;

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);
        $suffix = $this->processValue($this->suffix, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->rtrim($value, $suffix);
        }

        return $this->append($value, $suffix);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);
        $suffix = $this->processValue($this->suffix, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->append($value, $suffix);
        }

        return $this->rtrim($value, $suffix);
    }

    protected function append(string $value, string $suffix): string
    {
        return $value.$suffix;
    }

    protected function rtrim(string $value, string $suffix): string
    {
        if (!str_ends_with($value, $suffix)) {
            return $value;
        }

        return substr($value, 0, strlen($value) - strlen($suffix));
    }
}
