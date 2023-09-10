<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\Util\Type;

final class PropertyAccessor extends Accessor
{
    public const NULL_ON_ERROR = 'null_on_error';

    private static $accessors = [];

    private static bool $initialized = false;

    /**
     * @var string[]
     */
    private $flags = [];

    public function __construct(
        private string $path,
        string ...$flags
    ) {
        $this->flags = $flags;
    }

    public static function registerAccessor(string $accessor): void
    {
        static::validateAccessor($accessor);

        foreach ($accessor::getSupportedTypes() as $type) {
            static::$accessors[$type] = $accessor;
        }
    }

    public static function getSupportedTypes(): array
    {
        return array_keys(static::$accessors);
    }

    public static function get(string $path, mixed $data, string ...$flags): mixed
    {
        if (static::$initialized === false) {
            static::initialize();
        }

        $pointer = $data;

        if (empty($path) || $pointer === null) {
            return $pointer;
        }

        foreach (explode('.', $path) as $field) {
            $accessor = static::getAccessor($pointer);

            if ($accessor === null) {
                if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                    throw new NotAccessableException(Type::getType($pointer));
                }

                return null;
            }

            $pointer = $accessor::get($field, $pointer, ...$flags);

            if ($pointer === null) {
                return null;
            }
        }

        return $pointer;
    }

    public static function getAccessor(mixed $value): ?Accessor
    {
        foreach (array_reverse(static::$accessors) as $type => $accessor) {
            if (Type::is($value, $type)) {
                return $accessor;
            }
        }

        return null;
    }

    private static function initialize(): void
    {
        static::registerAccessor(ObjectAccessor::class);
        static::registerAccessor(ArrayAccessor::class);
        static::registerAccessor(ContainerAccessor::class);
        static::registerAccessor(EncapsulationAccessor::class);

        static::$initialized = true;
    }

    private static function validateAccessor(string $accessor): void
    {
        if (
            !class_exists($accessor) ||
            !is_subclass_of($accessor, Accessor::class) ||
            (new \ReflectionClass($accessor))->isAbstract()
        ) {
            throw new \InvalidArgumentException(sprintf('Accessor must be class inheriting from %s. Got %s.', Accessor::class, Type::getDebugType($accessor)));
        }
    }

    public function getValue(mixed $data): mixed
    {
        return static::get($this->path, $data, ...$this->flags);
    }
}
