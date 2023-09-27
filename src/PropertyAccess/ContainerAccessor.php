<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\Container;
use Dustin\Encapsulation\ImmutableContainer;
use Dustin\ImpEx\PropertyAccess\Exception\InvalidDataException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Util\Type;

class ContainerAccessor extends Accessor
{
    public static function get(int $field, Container $container, AccessContext $context): mixed
    {
        return ArrayAccessor::get($field, $container->toArray(), $context);
    }

    public static function set(int $field, mixed $value, Container $container, AccessContext $context): void
    {
        if ($field < 0 || $field > count($container)) {
            if ($context->hasFlag(AccessContext::STRICT)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return;
        }

        $container->splice($field, 1, [$value]);
    }

    public static function push(mixed $value, Container $container): void
    {
        $container->add($value);
    }

    public static function merge(mixed $value, Container $data, AccessContext $context): void
    {
        $index = -1;
        foreach (static::valueToMerge($value) as $key => $valueToMerge) {
            ++$index;

            if (!is_numeric($key)) {
                if ($context->hasFlag(AccessContext::STRICT)) {
                    throw InvalidDataException::keyNotNumeric($key);
                }

                $key = $index;
            }

            if ($context->hasFlag(AccessContext::PUSH_ON_MERGE)) {
                static::push($valueToMerge, $data);

                continue;
            }

            $key = intval($key);
            $dataValue = static::get($key, $data, $context->createSubContext(AccessOperation::GET, $context->getPath()->copy()->add((string) $key))->removeFlag(AccessContext::STRICT));
            $subContext = $context->createSubContext(AccessOperation::SET, $context->getPath()->copy()->add((string) $key));

            if (static::isMergable($dataValue) && static::isMergable($valueToMerge)) {
                // TODO
                PropertyAccessor::merge('', $dataValue, $valueToMerge, ...$context->getFlags());
                static::set($key, $dataValue, $data, $subContext);
            } else {
                static::set($key, $valueToMerge, $data, $subContext);
            }
        }
    }

    public static function collect(Container $container): array
    {
        return $container->toArray();
    }

    public function supports(string $operation, mixed $value): bool
    {
        if (!Type::is($value, Container::class)) {
            return false;
        }

        return !(\in_array($operation, AccessOperation::WRITE_OPERATIONS) && Type::is($value, ImmutableContainer::class));
    }

    protected function getValue(string $field, mixed $value, AccessContext $context): mixed
    {
        if (!is_numeric($field)) {
            if ($context->hasFlag(AccessContext::STRICT)) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        return static::get(intval($field), $value, $context);
    }

    protected function setValue(string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        if (!is_numeric($field)) {
            if ($context->hasFlag(AccessContext::STRICT)) {
                throw new PropertyNotFoundException($context->getPath());
            }
        }

        static::set($field, $value, $data, $context);
    }

    protected function pushValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::push($value, $data);
    }

    protected function mergeValue(mixed $value, mixed &$data, AccessContext $context): void
    {
        static::merge($value, $data, $context);
    }

    protected function collectValues(mixed &$data, AccessContext $context): array
    {
        return static::collect($data);
    }
}
