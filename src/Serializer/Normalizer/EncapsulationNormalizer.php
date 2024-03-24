<?php

namespace Dustin\ImpEx\Serializer\Normalizer;

use Dustin\Encapsulation\AbstractEncapsulation;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;

class EncapsulationNormalizer extends ConversionNormalizer
{
    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        if (!\is_object($data)) {
            return false;
        }

        $targetClass = $this->getEncapsulationClass();

        if ($targetClass === null) {
            return $data instanceof AbstractEncapsulation;
        }

        $this->validateEncapsulationClass($targetClass);

        return $data instanceof $targetClass;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null): bool
    {
        $targetClass = $this->getEncapsulationClass();

        if ($targetClass === null) {
            return is_subclass_of($type, AbstractEncapsulation::class);
        }

        $this->validateEncapsulationClass($targetClass);

        return is_subclass_of($type, $targetClass) ||
            $type === $targetClass;
    }

    public function getSupportedTypes(?string $format): array
    {
        $targetClass = $this->getEncapsulationClass();

        if ($targetClass === null) {
            return parent::getSupportedTypes($format);
        }

        $this->validateEncapsulationClass($targetClass);

        return [$targetClass => false];
    }

    /**
     * Overwrite this method if you want to restrict support to your class.
     */
    public function getEncapsulationClass(): ?string
    {
        return null;
    }

    /**
     * @param array|false $allowedAttributes
     *
     * @return object
     *
     * @throws NotNormalizableValueException
     */
    protected function instantiateObject(array &$data, string $class, array &$context, \ReflectionClass $reflectionClass, array|bool $allowedAttributes, string $format = null)
    {
        if (($object = $this->extractObjectToPopulate($class, $context, self::OBJECT_TO_POPULATE)) !== null) {
            return $object;
        }

        if ($this->classDiscriminatorResolver && $mapping = $this->classDiscriminatorResolver->getMappingForClass($class)) {
            if (!isset($data[$mapping->getTypeProperty()])) {
                throw NotNormalizableValueException::createForUnexpectedDataType(sprintf('Type property "%s" not found for the abstract object "%s".', $mapping->getTypeProperty(), $class), null, ['string'], isset($context['deserialization_path']) ? $context['deserialization_path'].'.'.$mapping->getTypeProperty() : $mapping->getTypeProperty(), false);
            }

            $type = $data[$mapping->getTypeProperty()];
            if (($mappedClass = $mapping->getClassForType($type)) === null) {
                throw NotNormalizableValueException::createForUnexpectedDataType(sprintf('The type "%s" is not a valid value.', $type), $type, ['string'], isset($context['deserialization_path']) ? $context['deserialization_path'].'.'.$mapping->getTypeProperty() : $mapping->getTypeProperty(), true);
            }

            if ($mappedClass !== $class) {
                return $this->instantiateObject($data, $mappedClass, $context, new \ReflectionClass($mappedClass), $allowedAttributes, $format);
            }
        }

        return new $class();
    }

    /**
     *  @return array
     */
    protected function extractAttributes(object $object, string $format = null, array $context = [])
    {
        return $object->getFields();
    }

    private function validateEncapsulationClass(string $class): void
    {
        if (!is_subclass_of($class, AbstractEncapsulation::class)) {
            throw new \RuntimeException(sprintf('getEncapsulationClass() must return class name inheriting from %s.', AbstractEncapsulation::class));
        }
    }
}
