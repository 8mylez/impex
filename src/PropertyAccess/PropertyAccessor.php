<?php

declare(strict_types=1);

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidPathException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
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

    public static function get(string|array|int|Path $path, mixed $data, string ...$flags): mixed
    {
        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $context = static::createContext(AccessOperation::GET, ...$flags);

        return static::getValue($path, $data, $context);
    }

    public static function set(string|array|int|Path $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $context = static::createContext(AccessOperation::SET, ...$flags);

        static::setValue($path, $data, $value, $context);
    }

    public static function push(string|array|int|Path $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $context = static::createContext(AccessOperation::PUSH, ...$flags);

        static::pushValue($path, $data, $value, $context);
    }

    public static function merge(string|array|int|Path $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $context = static::createContext(AccessOperation::MERGE, ...$flags);

        static::mergeValue($path, $data, $value, $context);
    }

    public static function collect(string|array|int|Path $path, mixed &$data, string ...$flags): array
    {
        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $context = static::createContext(AccessOperation::COLLECT, ...$flags);

        return static::collectValues($path, $data, $context);
    }

    private static function getValue(Path $path, mixed $data, AccessContext $context): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $pointer = $data;

        if ($pointer === null) {
            return $pointer;
        }

        $currentPath = new Path();

        foreach ($path as $field) {
            $currentPath->add($field);

            $pointer = static::access($field, $pointer, null, $context->subContext(AccessOperation::GET, $currentPath));

            if ($pointer === null) {
                return null;
            }
        }

        return $pointer;
    }

    private static function setValue(Path $path, mixed &$data, mixed $value, AccessContext $context): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if ($path->isEmpty()) {
            throw InvalidPathException::emptyPath();
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot set value to a null.');
        }

        $chain = static::readChain($path, $data, $context);

        $chain[count($chain) - 1]['value'] = $value;

        static::writeChain($chain, $data, $context);
    }

    private static function pushValue(Path $path, mixed &$data, mixed $value, AccessContext $context): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot push to a null value.');
        }

        if ($path->isEmpty()) {
            static::access(null, $data, $value, $context);

            return;
        }

        $chain = static::readChain($path, $data, $context);
        $pointer = &$chain[count($chain) - 1]['value'];

        static::access(null, $pointer, $value, $context);
        static::writeChain($chain, $data, $context);
    }

    private static function mergeValue(Path $path, mixed &$data, mixed $value, AccessContext $context): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot merge to a null value.');
        }

        if ($path->isEmpty()) {
            static::access(null, $data, $value, $context);

            return;
        }

        $chain = static::readChain($path, $data, $context);
        $pointer = &$chain[count($chain) - 1]['value'];

        static::access(null, $pointer, $value, $context);
        static::writeChain($chain, $data, $context);
    }

    private static function collectValues(Path $path, mixed $data, AccessContext $context): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot collect from a null value.');
        }

        $result = [];

        if (!static::hasCollector($path)) {
            $subContext = $context->subContext(AccessOperation::GET, new Path())->setFlag(AccessContext::STRICT);

            $resultPath = $context->getPath()->merge($path);
            try {
                $result[(string) $resultPath] = static::getValue($path, $data, $subContext);
            } catch (NotAccessableException|PropertyNotFoundException $e) {
                if ($context->hasFlag(AccessContext::STRICT)) {
                    throw $e;
                }

                return [];
            }

            return $context->hasFlag(AccessContext::COLLECT_NESTED) ? ArrayUtil::flatToNested($result) : $result;
        }

        $pointer = $data;
        $currentPath = new Path();
        $path = $path->toArray();

        while (($field = array_shift($path)) !== null) {
            if ($field !== AccessContext::COLLECTOR_FIELD) {
                $currentPath->add($field);
                $pointer = static::access($field, $pointer, null, $context->subContext(AccessOperation::GET, $currentPath));

                if ($pointer === null) {
                    return $result;
                }

                continue;
            }

            try {
                $pointer = static::access(null, $pointer, null, $context->subContext(AccessOperation::COLLECT, $currentPath));
            } catch (NotAccessableException $e) {
                if ($context->hasFlag(AccessContext::STRICT)) {
                    throw $e;
                }

                return [];
            }

            foreach ($pointer as $index => $item) {
                if (empty($path)) {
                    $itemPath = $context->getPath()->merge($currentPath)->add((string) $index);
                    $result[(string) $itemPath] = $item;

                    continue;
                }

                $subContext = $context->subContext(AccessOperation::COLLECT, $currentPath->copy()->add((string) $index))->removeFlag(AccessContext::COLLECT_NESTED);
                $itemResult = static::collectValues(new Path($path), $item, $subContext);

                $result = array_merge($result, $itemResult);
            }

            return $context->hasFlag(AccessContext::COLLECT_NESTED) ? ArrayUtil::flatToNested($result) : $result;
        }

        return $context->hasFlag(AccessContext::COLLECT_NESTED) ? ArrayUtil::flatToNested($result) : $result;
    }

    private static function access(int|string|null $field, mixed &$data, mixed $value, AccessContext $context): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $accessor = static::getAccessor($context->getOperation(), $data);

        if ($accessor === null) {
            if (!$context->hasFlag(AccessContext::STRICT)) {
                return null;
            }

            $path = $context->getPath()->copy();
            $path->pop();

            throw new NotAccessableException($path, Type::getDebugType($data), $context->getOperation());
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
            $pointer = static::access($field, $pointer, null, $context->subContext(AccessOperation::GET, $currentPath));
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

            static::access($field, $currentValue, $currentElement['value'], $context->subContext(AccessOperation::SET, static::createPathFromReverse($context->getPath(), $currentPath)));

            $currentPath->add($field);
            $currentElement = [
                'field' => $record['field'],
                'value' => $currentValue,
            ];
        }

        static::access($currentElement['field'], $data, $currentElement['value'], $context->subContext(AccessOperation::SET, static::createPathFromReverse($context->getPath(), $currentPath)));
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
        $path = $path->copy();
        $subPath = new Path(array_reverse($subPath->toArray()));
        $result = new Path();

        while (($field = $path->shift()) !== null) {
            $result->add($field);

            if ($path->equals($subPath)) {
                return $result;
            }
        }

        return $result;
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

    private static function createContext(string $operation, string ...$flags): AccessContext
    {
        $context = new AccessContext(
            $operation,
            $operation,
            new Path(),
            ...$flags
        );

        $context->addAccess(
            AccessOperation::GET,
            new Access(
                function (Path $path, mixed $data, mixed $value = null, AccessContext $context) {
                    return static::getValue($path, $data, $context);
                }
            )
        );

        $context->addAccess(
            AccessOperation::SET,
            new Access(
                function (Path $path, mixed &$data, mixed $value = null, AccessContext $context) {
                    return static::setValue($path, $data, $value, $context);
                }
            )
        );

        $context->addAccess(
            AccessOperation::PUSH,
            new Access(
                function (Path $path, mixed &$data, mixed $value = null, AccessContext $context) {
                    return static::pushValue($path, $data, $value, $context);
                }
            )
        );

        $context->addAccess(
            AccessOperation::MERGE,
            new Access(
                function (Path $path, mixed &$data, mixed $value = null, AccessContext $context) {
                    return static::mergeValue($path, $data, $value, $context);
                }
            )
        );

        $context->addAccess(
            AccessOperation::COLLECT,
            new Access(
                function (Path $path, mixed $data, mixed $value = null, AccessContext $context) {
                    return static::collectValues($path, $data, $context);
                }
            )
        );

        return $context;
    }
}
