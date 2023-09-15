<?php

namespace Dustin\ImpEx\PropertyAccess;

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

    public static function get(string $path, mixed $data, string ...$flags): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $pointer = $data;

        if (empty($path) || $pointer === null) {
            return $pointer;
        }

        $context = new AccessContext(
            AccessContext::GET,
            AccessContext::GET,
            $path,
            ...$flags
        );

        $currentPath = [];

        foreach (explode('.', $path) as $field) {
            $currentPath[] = $field;

            $pointer = static::access($field, $pointer, null, $context->createSubContext(AccessContext::GET, implode('.', $currentPath)));

            if ($pointer === null) {
                return null;
            }
        }

        return $pointer;
    }

    public static function set(string $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if (empty($path)) {
            throw new \InvalidArgumentException('Path cannot be empty.');
        }

        if ($data === null) {
            throw new \InvalidArgumentException('Cannot set value to null.');
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

    public static function push(string $path, mixed &$data, mixed $value, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
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
                throw new NotAccessableException($context->getPath(), Type::getDebugType($value));
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

    private static function initialize(): void
    {
        static::$initialized = true;

        static::registerAccessor(new ObjectAccessor());
        static::registerAccessor(new ArrayAccessor());
        static::registerAccessor(new ContainerAccessor());
        static::registerAccessor(new EncapsulationAccessor());
    }

    private static function createPathFromReverse(string $path, string $subPath): string
    {
        $subPath = implode('.', array_reverse(explode('.', $subPath)));

        return trim(substr($path, 0, strrpos($path, $subPath)), '.');
    }

    private static function readChain(string $path, mixed $data, AccessContext $context): array
    {
        $chain = [];
        $currentPath = [];
        $pointer = $data;

        foreach (explode('.', $path) as $field) {
            $currentPath[] = $field;
            $pointer = static::access($field, $pointer, null, $context->createSubContext(AccessContext::GET, implode('.', $currentPath)));
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
        $currentPath = [];

        foreach (array_reverse($chain) as $record) {
            $currentValue = $record['value'];
            $field = $currentElement['field'];

            static::access($field, $currentValue, $currentElement['value'], $context->createSubContext(AccessContext::SET, static::createPathFromReverse($context->getPath(), implode('.', $currentPath))));

            $currentPath[] = $field;
            $currentElement = [
                'field' => $record['field'],
                'value' => $currentValue,
            ];
        }

        static::access($currentElement['field'], $data, $currentElement['value'], $context->createSubContext(AccessContext::SET, static::createPathFromReverse($context->getPath(), implode('.', $currentPath))));
    }
}
