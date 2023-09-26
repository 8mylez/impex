<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidPathException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

final class PropertyAccessor
{
    private static $accessors = [];

    private static bool $initialized = false;

    public static function registerAccessor(Accessor $accessor): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        static::$accessors[] = $accessor;
    }

    public static function supports(string $operation, mixed $value): bool
    {
        return static::getAccessor($operation, $value) !== null;
    }

    public static function get(string|array|Path $path, mixed $data, string ...$flags): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $pointer = $data;

        if ($pointer === null) {
            return $pointer;
        }

        $context = new AccessContext(
            AccessOperation::GET,
            AccessOperation::GET,
            $path,
            ...$flags
        );

        $currentPath = new Path();

        foreach ($path as $field) {
            $currentPath->add($field);

            $pointer = static::access($field, $pointer, null, $context->createSubContext(AccessOperation::GET, $currentPath));

            if ($pointer === null) {
                return null;
            }
        }

        return $pointer;
    }

    public static function set(string|array|Path $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        if ($path->isEmpty()) {
            throw InvalidPathException::emptyPath();
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot set value to a null.');
        }

        $context = new AccessContext(
            AccessOperation::SET,
            AccessOperation::SET,
            $path,
            ...$flags
        );

        $chain = static::readChain($path, $data, $context);

        $chain[count($chain) - 1]['value'] = $value;

        static::writeChain($chain, $data, $context);
    }

    public static function push(string|array|Path $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot push to a null value.');
        }

        $context = new AccessContext(
            AccessOperation::PUSH,
            AccessOperation::PUSH,
            $path,
            ...$flags
        );

        if ($path->isEmpty()) {
            static::access(null, $data, $value, $context);

            return;
        }

        $chain = static::readChain($path, $data, $context);
        $pointer = &$chain[count($chain) - 1]['value'];

        static::access(null, $pointer, $value, $context);
        static::writeChain($chain, $data, $context);
    }

    public static function merge(string|array|Path $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot merge to a null value.');
        }

        $context = new AccessContext(
            AccessOperation::MERGE,
            AccessOperation::MERGE,
            $path,
            ...$flags
        );

        if ($path->isEmpty()) {
            static::access(null, $data, $value, $context);

            return;
        }

        $chain = static::readChain($path, $data, $context);
        $pointer = &$chain[count($chain) - 1]['value'];

        static::access(null, $pointer, $value, $context);
        static::writeChain($chain, $data, $context);
    }

    public static function collect(string|array|Path $path, mixed &$data, string ...$flags): array
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot collect from a null value.');
        }

        $context = new AccessContext(
            AccessOperation::COLLECT,
            AccessOperation::COLLECT,
            $path,
            ...$flags
        );

        $result = [];

        if (!static::hasCollector($path)) {
            $result[(string) $path] = static::get($path, $data, ...$flags);

            return $context->hasFlag(AccessContext::COLLECT_NESTED) ? ArrayUtil::flatToNested($result) : $result;
        }

        $pointer = $data;
        $currentPath = new Path();
        $path = $path->toArray();

        while (($field = array_shift($path)) !== null) {
            if ($field !== AccessContext::COLLECTOR_FIELD) {
                $pointer = static::get($field, $pointer, ...$context->getFlags());

                if ($pointer === null) {
                    return $result;
                }

                $currentPath->add($field);

                continue;
            }

            try {
                $pointer = static::access(null, $pointer, null, $context->createSubContext(AccessOperation::COLLECT, $currentPath));
            } catch (NotAccessableException $e) {
                if ($context->hasFlag(AccessContext::NULL_ON_ERROR)) {
                    return $result;
                }

                throw $e;
            }

            foreach ($pointer as $index => $item) {
                $itemPath = (new Path($currentPath->toArray()))->add($index);

                if (empty($path)) {
                    $result[(string) $itemPath] = $item;

                    continue;
                }

                $subContext = $context->createSubContext(AccessOperation::COLLECT, $itemPath)->removeFlag(AccessContext::COLLECT_NESTED);
                $itemResult = static::collect($path, $item, ...$subContext->getFlags());

                foreach ($itemResult as $itemResultIndex => $value) {
                    $itemResultPath = (new Path($itemPath->toArray()))->add($itemResultIndex);
                    $result[(string) $itemResultPath] = $value;
                }
            }

            return $context->hasFlag(AccessContext::COLLECT_NESTED) ? ArrayUtil::flatToNested($result) : $result;
        }

        return $context->hasFlag(AccessContext::COLLECT_NESTED) ? ArrayUtil::flatToNested($result) : $result;
    }

    private static function access(?string $field, mixed &$data, mixed $value, AccessContext $context): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $accessor = static::getAccessor($context->getOperation(), $data);

        if ($accessor === null) {
            if (!$context->hasFlag(AccessContext::NULL_ON_ERROR)) {
                throw new NotAccessableException($context->getPath(), Type::getDebugType($data), $context->getOperation());
            }

            return null;
        }

        return $accessor->access($field, $data, $value, $context);
    }

    private static function getAccessor(string $operation, mixed $value): ?Accessor
    {
        foreach (array_reverse(static::$accessors) as $accessor) {
            if ($accessor->supports($operation, $value)) {
                return $accessor;
            }
        }

        return null;
    }

    private static function readChain(Path $path, mixed $data, AccessContext $context): array
    {
        $chain = [];
        $currentPath = new Path();
        $pointer = $data;

        foreach ($path as $field) {
            $currentPath->add($field);
            $pointer = static::access($field, $pointer, null, $context->createSubContext(AccessOperation::GET, $currentPath));
            $chain[] = [
                'field' => $field,
                'value' => $pointer,
            ];
        }

        return $chain;
    }

    private static function writeChain(array $chain, mixed &$data, AccessContext $context): void
    {
        $currentElement = array_pop($chain);
        $currentPath = new Path();

        foreach (array_reverse($chain) as $record) {
            $currentValue = $record['value'];
            $field = $currentElement['field'];

            static::access($field, $currentValue, $currentElement['value'], $context->createSubContext(AccessOperation::SET, static::createPathFromReverse($context->getPath(), $currentPath)));

            $currentPath->add($field);
            $currentElement = [
                'field' => $record['field'],
                'value' => $currentValue,
            ];
        }

        static::access($currentElement['field'], $data, $currentElement['value'], $context->createSubContext(AccessOperation::SET, static::createPathFromReverse($context->getPath(), $currentPath)));
    }

    private static function initialize(): void
    {
        static::$initialized = true;

        static::registerAccessor(new ObjectAccessor());
        static::registerAccessor(new ArrayAccessor());
        static::registerAccessor(new ContainerAccessor());
        static::registerAccessor(new EncapsulationAccessor());
    }

    private static function createPathFromReverse(Path $path, Path $subPath): Path
    {
        $subPath = (string) new Path(array_reverse($subPath->toArray()));

        return new Path(trim(substr($path, 0, strrpos($path, $subPath)), '.'));
    }

    private static function hasCollector(Path $path): bool
    {
        foreach ($path as $field) {
            if ($field === AccessContext::COLLECTOR_FIELD) {
                return true;
            }
        }

        return false;
    }
}
