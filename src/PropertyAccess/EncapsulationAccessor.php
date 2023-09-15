<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\Encapsulation\Exception\PropertyNotExistsException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class EncapsulationAccessor extends Accessor
{
    public static function get(string $field, EncapsulationInterface $value, AccessContext $context): mixed
    {
        if (!$value->has($field)) {
            if (AccessContext::isWriteOperation($context->getRootOperation()) || $context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                return null;
            }

            throw new PropertyNotFoundException($context->getPath());
        }

        return $value->get($field);
    }

    public static function set(string $field, mixed $value, EncapsulationInterface $data, AccessContext $context): void
    {
        try {
            $data->set($field, $value);
        } catch (PropertyNotExistsException $e) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new PropertyNotFoundException($context->getPath());
            }
        }
    }

    public function supports(string $operation, mixed $value): bool
    {
        if ($operation === AccessContext::PUSH) {
            return false;
        }

        return Type::is($value, EncapsulationInterface::class);
    }

    public function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        return static::get($field, $value, $context);
    }

    public function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        static::set($field, $value, $data, $context);
    }
}
