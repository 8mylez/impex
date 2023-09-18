<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidPathException;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
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
        return static::getAccessor($value, $operation) !== null;
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
            AccessContext::GET,
            AccessContext::GET,
            $path,
            ...$flags
        );

        $currentPath = new Path();

        foreach ($path as $field) {
            $currentPath->add($field);

            $pointer = static::access($field, $pointer, null, $context->createSubContext(AccessContext::GET, $currentPath));

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
            AccessContext::SET,
            AccessContext::SET,
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
            throw new \InvalidArgumentException('Cannot push to null value.');
        }

        $context = new AccessContext(
            AccessContext::PUSH,
            AccessContext::PUSH,
            $path,
            ...$flags
        );

        $chain = static::readChain($path, $data, $context);

        $pointer = $chain[count($chain) - 1]['value'];

        static::access(null, $pointer, $value, $context);
        static::writeChain($chain, $data, $context);
    }

    private static function access(?string $field, mixed &$data, mixed $value, AccessContext $context): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $accessor = static::getAccessor($data, $context->getOperation());

        if ($accessor === null) {
            if (!$context->hasFlag(AccessContext::FLAG_NULL_ON_ERROR)) {
                throw new NotAccessableException($context->getPath(), Type::getDebugType($data));
            }

            return null;
        }

        return $accessor->access($field, $data, $value, $context);
    }

    private static function getAccessor(mixed $value, string $operation): ?Accessor
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
            $pointer = static::access($field, $pointer, null, $context->createSubContext(AccessContext::GET, $currentPath));
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

            static::access($field, $currentValue, $currentElement['value'], $context->createSubContext(AccessContext::SET, static::createPathFromReverse($context->getPath(), $currentPath)));

            $currentPath->add($field);
            $currentElement = [
                'field' => $record['field'],
                'value' => $currentValue,
            ];
        }

        static::access($currentElement['field'], $data, $currentElement['value'], $context->createSubContext(AccessContext::SET, static::createPathFromReverse($context->getPath(), $currentPath)));
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
}
