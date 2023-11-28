<?php

namespace Dustin\ImpEx\Serializer\Converter\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\OperationNotSupportedException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\ProcessValueTrait;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;

class AccessOperationConverter extends UnidirectionalConverter
{
    use ProcessValueTrait;

    public function __construct(private AccessOperation $operation, private mixed $writeValue = null)
    {
        if ($writeValue instanceof AccessOperation && !AccessOperation::isReadOperation($writeValue)) {
            throw new \LogicException('Operation must be read-operation.');
        }
    }

    public static function get(string|array|Path $path, string ...$flags): self
    {
        return new self(new AccessOperation($path, AccessOperation::GET, ...$flags));
    }

    public static function set(string|array|Path $path, mixed $value, string ...$flags): self
    {
        return new self(new AccessOperation($path, AccessOperation::SET, ...$flags), $value);
    }

    public static function push(string|array|Path $path, mixed $value, string ...$flags): self
    {
        return new self(new AccessOperation($path, AccessOperation::PUSH, ...$flags), $value);
    }

    public static function merge(string|array|Path $path, mixed $value, string ...$flags): self
    {
        return new self(new AccessOperation($path, AccessOperation::MERGE, ...$flags), $value);
    }

    public static function collect(string|array|Path $path, string ...$flags): self
    {
        return new self(new AccessOperation($path, AccessOperation::COLLECT, ...$flags));
    }

    public static function has(string|array|Path $path, string ...$flags): self
    {
        return new self(new AccessOperation($path, AccessOperation::HAS, ...$flags));
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        $writeValue = $this->processValue($this->writeValue, $context);

        try {
            $this->operation->execute($value, $writeValue);
        } catch (InvalidDataException|NotAccessableException|OperationNotSupportedException|PropertyNotFoundException $exception) {
            throw AttributeConversionException::fromErrorCode($exception, $context);
        }
    }
}
