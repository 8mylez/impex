<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;

class SwitchCase extends BidirectionalConverter
{
    /**
     * @var array
     */
    private $cases = [];

    public function __construct(private ?AttributeConverter $defaultConverter = null, ValueCase ...$cases)
    {
        $this->cases = $cases;
    }

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        foreach ($this->cases as $case) {
            if ($case->isFullfilled($value, $object, $path, $attributeName)) {
                return $case->getConverter()->normalize($value, $object, $path, $attributeName);
            }
        }

        if ($this->defaultConverter !== null) {
            return $this->defaultConverter->normalize($value, $object, $path, $attributeName);
        }

        return $value;
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $normalizedData)
    {
        foreach ($this->cases as $case) {
            if ($case->isFullfilled($value, $object, $path, $attributeName)) {
                return $case->getConverter()->denormalize($value, $object, $path, $attributeName, $normalizedData);
            }
        }

        if ($this->defaultConverter !== null) {
            return $this->defaultConverter->denormalize($value, $object, $path, $attributeName, $normalizedData);
        }

        return $value;
    }
}
