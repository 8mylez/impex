<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

class ConditionalConverter extends BidirectionalConverter
{
    /**
     * @var Condition
     */
    private $condition;

    /**
     * @var AttributeConverter
     */
    private $fulfilledConverter;

    /**
     * @var AttributeConverter|null
     */
    private $unfulfilledConverter;

    public function __construct(
        Condition $condition,
        AttributeConverter $fulfilledConverter,
        ?AttributeConverter $unfulfilledConverter = null
    ) {
        $this->condition = $condition;
        $this->fulfilledConverter = $fulfilledConverter;
        $this->unfulfilledConverter = $unfulfilledConverter;
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->condition->isFullfilled($value, $object, $path, $attributeName)) {
            return $this->fulfilledConverter->normalize($value, $object, $path, $attributeName);
        }

        if ($this->unfulfilledConverter !== null) {
            return $this->unfulfilledConverter->normalize($value, $object, $path, $attributeName);
        }

        return $value;
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $data)
    {
        if ($this->condition->isFullfilled($value, $object, $path, $attributeName)) {
            return $this->fulfilledConverter->denormalize($value, $object, $path, $attributeName, $data);
        }

        if ($this->unfulfilledConverter !== null) {
            return $this->unfulfilledConverter->denormalize($value, $object, $path, $attributeName, $data);
        }

        return $value;
    }
}
