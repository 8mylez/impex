<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\AbstractEncapsulation;
use Dustin\Encapsulation\Encapsulation;
use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Util\ArrayUtil;
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
     * @param string $encapsulationClass The class to create an object from. Must inherit from {@see} AbstractEncapsulation
     *
     * @throws \InvalidArgumentException Thrown if the given class does not inherit from {@see} AbstractEncapsulation
     */
    public function __construct(
        private string $encapsulationClass = Encapsulation::class,
        string ...$flags
    ) {
        if (!is_subclass_of($encapsulationClass, AbstractEncapsulation::class)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not inherit from %s', $encapsulationClass, AbstractEncapsulation::class));
        }

        parent::__construct(...$flags);
    }

    /**
     * @param EncapsulationInterface|null $value
     *
     * @throws InvalidTypeException
     */
    public function normalize(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->validateType($value, EncapsulationInterface::class, $context);

        return $value->toArray();
    }

    /**
     * @param array|null $data
     *
     * @throws InvalidTypeException
     */
    public function denormalize(mixed $data, ConversionContext $context): AbstractEncapsulation|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $data = ArrayUtil::ensure($data);
        }

        $this->validateType($data, Type::ARRAY, $context);

        $class = $this->encapsulationClass;

        return new $class($data);
    }
}
