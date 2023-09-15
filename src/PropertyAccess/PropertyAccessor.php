<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\Util\Type;

final class PropertyAccessor
{
    public const NULL_ON_ERROR = 'null_on_error';

    private const MODE_GET = 'Get';

    private const MODE_SET = 'Set';

    private const MODE_PUSH = 'Push';

    private static $accessors = [];

    private static bool $initialized = false;

    public static function registerAccessor(Accessor $accessor): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        static::$accessors[] = $accessor;
    }

    public static function supportsSetAccess(mixed $value, string $field): bool
    {
        if (strpos($field, '.') !== false) {
            throw new \RuntimeException('Support for paths cannot be detected.');
        }

        return static::getAccessor($value, self::MODE_SET) !== null;
    }

    public static function supportsGetAccess(mixed $value, string $field): bool
    {
        if (strpos($field, '.') !== false) {
            throw new \RuntimeException('Support for paths cannot be detected.');
        }

        return static::getAccessor($value, self::MODE_GET) !== null;
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

        $currentPath = '';

        foreach (explode('.', $path) as $field) {
            $currentPath = trim($currentPath .= ".$field", '.');

            $pointer = static::getValueOf($field, $pointer, $currentPath, ...$flags);

            if ($pointer === null) {
                return null;
            }
        }

        return $pointer;
    }

    public static function set(string $path, mixed $value, mixed &$data, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $pointer = $data;

        if (empty($path) || $pointer === null) {
            // Todo: exception
            return;
        }

        $chain = static::readChain($path, $data, ...$flags);
        $chain[count($chain) - 1]['value'] = $value;

        static::writeChain($chain, $data, $path, ...$flags);
    }

    public static function push(string $path, mixed $value, mixed &$data, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $pointer = $data;

        if (empty($path) || $pointer === null) {
            // Todo: exception
            return;
        }

        $chain = static::readChain($path, $data, ...$flags);
        $pointer = $chain[count($chain) - 1]['value'];

        static::pushValue($value, $pointer, $path, ...$flags);
        static::writeChain($chain, $data, $path, ...$flags);
    }

    public static function getValueOf(string $field, mixed $value, ?string $path, string ...$flags): mixed
    {
        if (!static::$initialized) {
            static::initialize();
        }

        if ($path === null) {
            $path = $field;
        }

        $accessor = static::getAccessor($value, self::MODE_GET);

        if ($accessor === null) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new NotAccessableException($path, Type::getDebugType($value));
            }

            return null;
        }

        return $accessor->getValue($field, $value, $path, ...$flags);
    }

    public static function setValueOf(string $field, mixed $value, mixed &$data, ?string $path, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $accessor = static::getAccessor($data, self::MODE_SET);

        if ($accessor === null) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new NotAccessableException($path, Type::getDebugType($data));
            }

            return;
        }

        $accessor->setValue($field, $value, $data, $path, ...$flags);
    }

    public static function pushValue(mixed $value, mixed &$data, string $path, string ...$flags): void
    {
        if (!static::$initialized) {
            static::initialize();
        }

        $accessor = static::getAccessor($data, self::MODE_PUSH);

        if ($accessor === null) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new NotAccessableException($path, Type::getDebugType($data));
            }

            return;
        }

        $accessor->setValue(Field::OPERATOR_PUSH, $value, $data, $path, ...$flags);
    }

    private static function getAccessor(mixed $value, string $mode = self::MODE_GET): ?Accessor
    {
        $supportsMethod = 'supports'.$mode;

        foreach (array_reverse(static::$accessors) as $accessor) {
            if ($accessor->$supportsMethod($value)) {
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

    private static function hasFlag(string $flag, array $flags): bool
    {
        return \in_array($flag, $flags);
    }

    private static function readChain(string $path, mixed $data, string ...$flags): array
    {
        $chain = [];
        $currentPath = [];
        $pointer = $data;

        foreach (explode('.', $path) as $field) {
            $currentPath[] = $field;
            $pointer = static::getValueOf($field, $pointer, implode('.', $currentPath), ...$flags);
            $chain[] = [
                'field' => $field,
                'value' => $pointer,
            ];
        }

        return $chain;
    }

    private static function writeChain(array $chain, mixed &$data, string $path, string ...$flags): void
    {
        $currentElement = array_pop($chain);
        $currentPath = [];

        foreach (array_reverse($chain) as $record) {
            $currentValue = $record['value'];
            $field = $currentElement['field'];

            static::setValueOf($field, $currentElement['value'], $currentValue, static::createPathFromReverse($path, implode('.', $currentPath)), ...$flags);

            $currentPath[] = $field;
            $currentElement = [
                'field' => $record['field'],
                'value' => $currentValue,
            ];
        }

        static::setValueOf($currentElement['field'], $currentElement['value'], $data, static::createPathFromReverse($path, implode('.', $currentPath)), ...$flags);
    }
}
