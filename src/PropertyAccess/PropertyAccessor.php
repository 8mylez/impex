<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\Encapsulation\Container;
use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\PropertyAccess\Exception\PropertyNotFoundException;
use Dustin\ImpEx\Util\Type;

class PropertyAccessor
{
    public const NULL_ON_ERROR = 'null_on_error';

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

    public static function access(string $path, mixed $data, string ...$flags): mixed
    {
        $pointer = $data;

        if (empty($path) || $pointer === null) {
            return $pointer;
        }

        foreach (explode('.', $path) as $field) {
            if (is_numeric($field)) {
                $field = (int) $field;
            }

            if ($pointer instanceof EncapsulationInterface) {
                $pointer = static::fromEncapsulation((string) $field, $pointer, $flags);
            } elseif ($pointer instanceof Container) {
                if (!is_int($field)) {
                    if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                        throw new PropertyNotFoundException($field);
                    }

                    return null;
                }

                $pointer = static::fromContainer((int) $field, $pointer);
            } elseif (\is_array($pointer)) {
                $pointer = static::fromArray($field, $pointer, $flags);
            } elseif (\is_object($pointer)) {
                $pointer = static::fromObject((string) $field, $pointer, $flags);
            } else {
                if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                    throw new NotAccessableException(Type::getType($pointer));
                }

                $pointer = null;
            }

            if ($pointer === null) {
                return null;
            }
        }

        return $pointer;
    }

    protected static function fromEncapsulation(string $field, EncapsulationInterface $data, array $flags): mixed
    {
        if (!$data->has($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($field);
            }

            return null;
        }

        return $data->get($field);
    }

    protected static function fromContainer(int $index, Container $data): mixed
    {
        return $data->getAt($index);
    }

    protected static function fromArray(int|string $field, array $data, array $flags): mixed
    {
        if (!array_key_exists($field, $data)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException((string) $field);
            }

            return null;
        }

        return $data[$field];
    }

    protected static function fromObject(string $field, object $data, array $flags): mixed
    {
        $reflectionObject = new \ReflectionObject($data);

        if (!$reflectionObject->hasProperty($field)) {
            if (!static::hasFlag(self::NULL_ON_ERROR, $flags)) {
                throw new PropertyNotFoundException($field);
            }

            return null;
        }

        $property = $reflectionObject->getProperty($field);
        $property->setAccessible(true);

        if ($property->hasType() && !$property->isInitialized($data)) {
            return null;
        }

        return $property->getValue($data);
    }

    public function getValue($data): mixed
    {
        return static::access($this->path, $data, ...$this->flags);
    }

    private static function hasFlag(string $flag, array $flags): bool
    {
        return in_array($flag, $flags);
    }
}
