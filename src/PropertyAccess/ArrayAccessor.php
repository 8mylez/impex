<?php

declare(strict_types=1);

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Util\Type;

class ArrayAccessor extends Accessor
{
    public const MERGE_OVERWRITE_NUMERIC = 'merge_overwrite_numeric';

    public static function get(int|string $field, array $value, AccessContext $context): mixed
    {
        if (!array_key_exists($field, $value)) {
            if ($context->hasFlag(AccessContext::STRICT) && !AccessOperation::isWriteOperation($context->getRootOperation())) {
                throw new PropertyNotFoundException($context->getPath());
            }

            return null;
        }

        return $value[$field];
    }

    public static function set(int|string $field, mixed $value, array &$data): void
    {
        $data[$field] = $value;
    }

    public static function push(mixed $value, array &$data): void
    {
        $data[] = $value;
    }

    public static function merge(mixed $value, array &$data, AccessContext $context): void
    {
        foreach (static::valueToMerge($value) as $key => $valueToMerge) {
            $dataValue = static::get($key, $data, $context->subContext(AccessOperation::GET, new Path([$key])));

            if (static::isMergable($dataValue) && static::isMergable($valueToMerge)) {
                $context->subContext(AccessOperation::MERGE, new Path([$key]))->access([], $dataValue, $valueToMerge);
                static::set($key, $dataValue, $data);
            } else {
                if (is_int($key) && !$context->hasFlag(self::MERGE_OVERWRITE_NUMERIC)) {
                    static::push($valueToMerge, $data);
                } else {
                    static::set($key, $valueToMerge, $data);
                }
            }
        }
    }

    public static function collect(array $data): array
    {
        return $data;
    }

    public function supports(string $operation, mixed $value): bool
    {
        return Type::is($value, Type::ARRAY);
    }

    protected function getValue(int|string $field, mixed $value, AccessContext $context): mixed
    {
        return static::get($field, $value, $context);
    }

    protected function setValue(int|string $field, mixed $value, mixed &$data, AccessContext $context): void
    {
        static::set($field, $value, $data);
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
