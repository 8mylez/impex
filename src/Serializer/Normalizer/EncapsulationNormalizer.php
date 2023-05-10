<?php

namespace Dustin\ImpEx\Serializer\Normalizer;

use Dustin\Encapsulation\AbstractEncapsulation;
use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\ContextProviderInterface;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Util\Value;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Mapping\AttributeMetadataInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EncapsulationNormalizer extends AbstractNormalizer implements ContextProviderInterface
{
    public const ENABLE_MAX_DEPTH = 'enable_max_depth';

    public const DEPTH_KEY_PATTERN = 'depth_%s::%s';

    public const SKIP_NULL_VALUES = 'skip_null_values';

    public const MAX_DEPTH_HANDLER = 'max_depth_handler';

    public const DEEP_OBJECT_TO_POPULATE = 'deep_object_to_populate';

    public const PRESERVE_EMPTY_OBJECTS = 'preserve_empty_objects';

    public const CONVERTERS = 'converters';

    /**
     * @var callable
     */
    protected $objectClassResolver;

    /**
     * @var ClassDiscriminatorResolverInterface|null
     */
    protected $classDiscriminatorResolver;

    protected array $currentContext = [];

    public function __construct(
        ClassMetadataFactoryInterface $classMetadataFactory = null,
        NameConverterInterface $nameConverter = null,
        ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        callable $objectClassResolver = null,
        array $defaultContext = []
    ) {
        if (!isset($defaultContext[self::GROUPS])) {
            $defaultContext[self::GROUPS] = ['*'];
        }

        parent::__construct($classMetadataFactory, $nameConverter, $defaultContext);

        $this->validateContext($defaultContext);

        if ($classDiscriminatorResolver === null && $classMetadataFactory !== null) {
            $classDiscriminatorResolver = new ClassDiscriminatorFromClassMetadata($classMetadataFactory);
        }

        $this->classDiscriminatorResolver = $classDiscriminatorResolver;
        $this->objectClassResolver = $objectClassResolver;
    }

    /**
     * @param mixed $data
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        if (!\is_object($data)) {
            return false;
        }

        $targetClass = $this->getEncapsulationClass();

        if ($targetClass === null) {
            return $data instanceof AbstractEncapsulation;
        }

        if ($this->allowSubClasses()) {
            return $data instanceof $targetClass;
        }

        return \get_class($data) === $targetClass;
    }

    /**
     * @param mixed $data
     */
    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        $targetClass = $this->getEncapsulationClass();

        if ($targetClass === null) {
            return is_subclass_of($type, AbstractEncapsulation::class);
        }

        if ($this->allowSubClasses()) {
            return is_subclass_of($type, $targetClass) ||
                $type === $targetClass;
        }

        return $type === $targetClass;
    }

    /**
     * Overwrite this method if you want to restrict support to your class.
     */
    public function getEncapsulationClass(): ?string
    {
        return null;
    }

    public function getContext(): array
    {
        return $this->currentContext;
    }

    /**
     * @param object $object
     *
     * @return array|\ArrayObject
     *
     * @throws \RuntimeException
     * @throws ExtraAttributesException
     * @throws NotNormalizableValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws CircularReferenceException
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $this->validateContext($context);
        $this->currentContext = $context;

        if ($this->isCircularReference($object, $context)) {
            $result = $this->handleCircularReference($object, $format, $context);
            $this->currentContext = [];

            return $result;
        }

        $class = $this->getObjectClass($object);
        $attributesMetadata = $this->getAttributesMetadata($class);
        $maxDepthHandler = $this->getMaxDepthHandler($context);

        $attributes = $this->getAttributes($object, $class, $format, $context);
        $data = [];
        $conversionExceptions = [];

        foreach ($attributes as $attribute) {
            $attributeContext = $this->getAttributeNormalizationContext($object, $attribute, $context);
            $attributeValue = $this->getAttributeValue($object, $attribute, $format, $attributeContext);

            if (
                $this->isMaxDepthEnabled($context) &&
                $this->isMaxDepthReached($attributesMetadata, $class, $attribute, $context)
            ) {
                if ($maxDepthHandler === null) {
                    continue;
                }

                $attributeValue = $maxDepthHandler($attributeValue, $object, $attribute, $format, $attributeContext);
            }

            $attributeValue = $this->applyCallbacks($attributeValue, $object, $attribute, $format, $attributeContext);

            if ($converter = $this->getConverter($attribute, $attributeContext)) {
                try {
                    $attributeValue = $converter->normalize($attributeValue, $object, '/'.$attribute, $attribute);
                } catch (AttributeConversionException $e) {
                    $conversionExceptions[] = $e;
                    continue;
                }
            }

            if (!Value::isNormalized($attributeValue)) {
                if (!($this->serializer instanceof NormalizerInterface)) {
                    throw new \RuntimeException(sprintf("Cannot normalize attribute '%s'. Use a serializer or add a converter.", $attribute));
                }

                $childContext = $this->createChildContext($attributeContext, $attribute, $format);
                $attributeValue = $this->serializer->normalize($attributeValue, $format, $childContext);
            }

            if ($attributeValue === null && $this->skipNullValues($context)) {
                continue;
            }

            if ($this->nameConverter !== null) {
                $attribute = $this->nameConverter->normalize($attribute, $class, $format, $context);
            }

            $data[$attribute] = $attributeValue;
        }

        $this->currentContext = [];

        if (count($conversionExceptions) > 0) {
            throw new AttributeConversionExceptionStack('', $object->toArray(), ...$conversionExceptions);
        }

        if ($this->preserveEmptyObjects($context) && empty($data)) {
            return new \ArrayObject();
        }

        return $data;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     *
     * @throws \RuntimeException
     * @throws ExtraAttributesException
     * @throws NotNormalizableValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $this->validateContext($context);
        $this->currentContext = $context;

        $data = $this->prepareForDenormalization($data);

        $object = $this->instantiateObject($data, $type, $context, new \ReflectionClass($type), false, $format);
        $class = $this->getObjectClass($object);
        $attributes = $this->getAttributes($object, $class, $format, $context);

        if (!$this->allowExtraAttributes($context)) {
            $extraAttributes = $this->getExtraAttributes($attributes, array_keys($data));

            if (!empty($extraAttributes)) {
                throw new ExtraAttributesException($extraAttributes);
            }
        }

        $conversionExceptions = [];

        foreach ($attributes as $attribute) {
            $normalizedAttribute = $attribute;

            if ($this->nameConverter) {
                $normalizedAttribute = $this->nameConverter->normalize($attribute, $class, $format, $context);
            }

            $attributeContext = $this->getAttributeDenormalizationContext($class, $attribute, $context);

            $value = $this->fetchValue($normalizedAttribute, $data, $context);
            $value = $this->applyCallbacks($value, $class, $attribute, $format, $attributeContext);

            if ($converter = $this->getConverter($attribute, $attributeContext)) {
                try {
                    $value = $converter->denormalize($value, $object, '/'.$attribute, $attribute, $data);
                } catch (AttributeConversionException $e) {
                    $conversionExceptions[] = $e;
                    continue;
                }
            }

            if ($this->deepObjectToPopulate($attributeContext)) {
                $deepObject = $this->getAttributeValue($object, $attribute, $format, $attributeContext);

                if (\is_object($deepObject)) {
                    if (!($this->serializer instanceof DenormalizerInterface)) {
                        throw new \RuntimeException(sprintf("Cannot denormalize deep object attribute '%s'. Use a serializer.", $attribute));
                    }

                    $attributeContext[self::OBJECT_TO_POPULATE] = $deepObject;
                    $value = $this->serializer->denormalize($value, $this->getObjectClass($deepObject), $format, $attributeContext);
                }
            }

            $this->setAttributeValue($object, $attribute, $value, $format, $attributeContext);
        }

        $this->currentContext = [];

        if (count($conversionExceptions) > 0) {
            throw new AttributeConversionExceptionStack('', $data, ...$conversionExceptions);
        }

        return $object;
    }

    /**
     * Returns wether the normalizer supports sub classes.
     */
    protected function allowSubClasses(): bool
    {
        return true;
    }

    /**
     * @param mixed $data
     *
     * @return array
     */
    protected function prepareForDenormalization($data)
    {
        if ($data instanceof EncapsulationInterface) {
            return $data->toArray();
        }

        return (array) $data;
    }

    protected function getObjectClass(object $object): string
    {
        return $this->objectClassResolver ? ($this->objectClassResolver)($object) : \get_class($object);
    }

    protected function getAttributesMetadata(string $class): array
    {
        return $this->classMetadataFactory ? $this->classMetadataFactory->getMetadataFor($class)->getAttributesMetadata() : [];
    }

    protected function getAttributes(object $object, string $class, ?string $format, array $context): array
    {
        $allowedAttributes = $this->getAllowedAttributes($object, $context, true);

        if ($allowedAttributes !== false) {
            return $allowedAttributes;
        }

        $attributes = $context[self::ATTRIBUTES] ?? $this->defaultContext[self::ATTRIBUTES] ?? [];

        if (empty($attributes)) {
            $attributes = $this->extractAttributes($object, $format, $context);
        }

        if ($this->classDiscriminatorResolver && $mapping = $this->classDiscriminatorResolver->getMappingForMappedObject($object)) {
            array_unshift($attributes, $mapping->getTypeProperty());
        }

        $attributes = array_filter($attributes, function (string $attribute) use ($object, $format, $context) {
            return $this->isAllowedAttribute($object, $attribute, $format, $context);
        });

        return $attributes;
    }

    /**
     * @param string|object $classOrObject
     *
     * @return array|false
     */
    protected function getAllowedAttributes($classOrObject, array $context, bool $attributesAsString = false)
    {
        if (!$this->classMetadataFactory) {
            return false;
        }

        return parent::getAllowedAttributes($classOrObject, $context, $attributesAsString);
    }

    protected function getExtraAttributes(array $attributes, array $normalizedAttributes): array
    {
        if ($this->nameConverter) {
            array_walk($normalizedAttributes, function (string &$a) { $a = $this->nameConverter->denormalize($a); });
            array_walk($attributes, function (string &$a) { $a = $this->nameConverter->denormalize($a); });
        }

        return array_diff($normalizedAttributes, $attributes);
    }

    /**
     * @param array|false $allowedAttributes
     *
     * @return object
     *
     * @throws NotNormalizableValueException
     */
    protected function instantiateObject(array &$data, string $class, array &$context, \ReflectionClass $reflectionClass, $allowedAttributes, string $format = null)
    {
        if (($object = $this->extractObjectToPopulate($class, $context, self::OBJECT_TO_POPULATE)) !== null) {
            return $object;
        }

        if ($this->classDiscriminatorResolver && $mapping = $this->classDiscriminatorResolver->getMappingForClass($class)) {
            if (!isset($data[$mapping->getTypeProperty()])) {
                throw NotNormalizableValueException::createForUnexpectedDataType(sprintf('Type property "%s" not found for the abstract object "%s".', $mapping->getTypeProperty(), $class), null, ['string'], isset($context['deserialization_path']) ? $context['deserialization_path'].'.'.$mapping->getTypeProperty() : $mapping->getTypeProperty(), false);
            }

            $type = $data[$mapping->getTypeProperty()];
            if (null === ($mappedClass = $mapping->getClassForType($type))) {
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

    /**
     * @return mixed
     */
    protected function getAttributeValue(object $object, string $attribute, string $format = null, array $context = [])
    {
        return $object->get($attribute);
    }

    protected function setAttributeValue(object $object, string $attribute, $value, string $format = null, array $context = []): void
    {
        $object->set($attribute, $value);
    }

    /**
     * @return mixed
     */
    protected function fetchValue(string $attribute, array $data, array $context)
    {
        return $data[$attribute] ?? null;
    }

    protected function getAttributeNormalizationContext(object $object, string $attribute, array $context): array
    {
        if (($metadata = $this->getAttributeMetadata($object, $attribute)) === null) {
            return $context;
        }

        return array_merge($context, $metadata->getNormalizationContextForGroups($this->getGroups($context)));
    }

    protected function getAttributeDenormalizationContext(string $class, string $attribute, array $context): array
    {
        $context['deserialization_path'] = ($context['deserialization_path'] ?? false) ? $context['deserialization_path'].'.'.$attribute : $attribute;

        if (($metadata = $this->getAttributeMetadata($class, $attribute)) === null) {
            return $context;
        }

        return array_merge($context, $metadata->getDenormalizationContextForGroups($this->getGroups($context)));
    }

    /**
     * @param string|object $objectOrClass
     */
    protected function getAttributeMetadata($objectOrClass, string $attribute): ?AttributeMetadataInterface
    {
        if (!$this->classMetadataFactory) {
            return null;
        }

        return $this->classMetadataFactory->getMetadataFor($objectOrClass)->getAttributesMetadata()[$attribute] ?? null;
    }

    protected function getConverter(string $attribute, array $context): ?AttributeConverter
    {
        return $context[self::CONVERTERS][$attribute] ?? $this->defaultContext[self::CONVERTERS][$attribute] ?? null;
    }

    protected function allowExtraAttributes(array $context): bool
    {
        return $context[self::ALLOW_EXTRA_ATTRIBUTES] ?? $this->defaultContext[self::ALLOW_EXTRA_ATTRIBUTES] ?? true;
    }

    protected function skipNullValues(array $context): bool
    {
        return $context[self::SKIP_NULL_VALUES] ?? $this->defaultContext[self::SKIP_NULL_VALUES] ?? false;
    }

    protected function isMaxDepthEnabled(array $context): bool
    {
        return $context[self::ENABLE_MAX_DEPTH] ?? $this->defaultContext[self::ENABLE_MAX_DEPTH] ?? false;
    }

    protected function getMaxDepthHandler(array $context): ?callable
    {
        return $context[self::MAX_DEPTH_HANDLER] ?? $this->defaultContext[self::MAX_DEPTH_HANDLER] ?? null;
    }

    protected function isMaxDepthReached(array $attributesMetadata, string $class, string $attribute, array &$context): bool
    {
        if (
            !isset($attributesMetadata[$attribute]) ||
            ($maxDepth = $attributesMetadata[$attribute]->getMaxDepth()) === null
        ) {
            return false;
        }

        $key = sprintf(self::DEPTH_KEY_PATTERN, $class, $attribute);

        if (!isset($context[$key])) {
            $context[$key] = 1;

            return false;
        }

        if ($context[$key] === $maxDepth) {
            return true;
        }

        ++$context[$key];

        return false;
    }

    protected function preserveEmptyObjects(array $context): bool
    {
        return $context[self::PRESERVE_EMPTY_OBJECTS] ?? $this->defaultContext[self::PRESERVE_EMPTY_OBJECTS] ?? false;
    }

    protected function deepObjectToPopulate(array $context): bool
    {
        return $context[self::DEEP_OBJECT_TO_POPULATE] ?? $this->defaultContext[self::DEEP_OBJECT_TO_POPULATE] ?? false;
    }

    private function validateContext(array $context)
    {
        $this->validateCallbackContext($context);

        if (isset($context[self::MAX_DEPTH_HANDLER]) && !\is_callable($context[self::MAX_DEPTH_HANDLER])) {
            throw new \InvalidArgumentException(sprintf("Context option '%s' must be callable", self::MAX_DEPTH_HANDLER));
        }

        if (isset($context[self::CONVERTERS])) {
            if (!\is_array($context[self::CONVERTERS])) {
                throw new \InvalidArgumentException(sprintf("Context option '%s' must be array of converters", self::CONVERTERS));
            }

            foreach ($context[self::CONVERTERS] as $attribute => $converter) {
                if (!\is_object($converter) || !($converter instanceof AttributeConverter)) {
                    $type = \is_object($converter) ? get_class($converter) : gettype($converter);

                    throw new \InvalidArgumentException(sprintf("Converter for attribute '%s' must be %s. Got %s", $attribute, AttributeConverter::class, $type));
                }
            }
        }

        if (!$this->classMetadataFactory && isset($context[self::GROUPS]) && $context[self::GROUPS] != ['*']) {
            throw new \LogicException(sprintf('A %s must be set when selecting groups.', ClassMetadataFactoryInterface::class));
        }
    }
}
