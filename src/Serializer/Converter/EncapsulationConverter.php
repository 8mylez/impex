<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\AbstractEncapsulation;
use Dustin\Encapsulation\Encapsulation;
use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Util\Type;

/**
 * Converts an array of data into an encapsulation.
 *
 * This converter builds an encapsulation object from a given array.
 * The encapsulation class must inherit from {@see} AbstractEncapsulation.
 */
class EncapsulationConverter extends BidirectionalConverter
{
    /**
     * @var string
     */
    private $encapsulationClass;

    /**
     * @param string $encapsulationClass The class to create an object from. Must inherit from {@see} AbstractEncapsulation
     *
     * @throws \InvalidArgumentException Thrown if the given class does not inherit from {@see} AbstractEncapsulation
     */
    public function __construct(
        string $encapsulationClass = Encapsulation::class,
        string ...$flags
    ) {
        if (!is_subclass_of($encapsulationClass, AbstractEncapsulation::class)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not inherit from %s', $encapsulationClass, AbstractEncapsulation::class));
        }

        $this->encapsulationClass = $encapsulationClass;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
        ];
    }

    /**
     * @param Encapsulated|null $value
     *
     * @return array|null
     *
     * @throws \InvalidArgumentException
     */
    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->validateType($value, EncapsulationInterface::class, $path, $object->toArray());

        return $value->toArray();
    }

    /**
     * @param array|null $data
     *
     * @return AbstractEncapsulation|null
     *
     * @throws InvalidTypeException
     */
    public function denormalize($data, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $data = (array) $data;
        }

        $this->validateType($data, Type::ARRAY, $path, $normalizedData);

        return new $this->encapsulationClass($data);
    }
}
