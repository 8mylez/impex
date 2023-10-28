<?php

namespace Dustin\ImpEx\Serializer\Normalizer;

use Dustin\Exception\ErrorCode;
use Dustin\ImpEx\PropertyAccess\Operation\AccessOperation;
use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;
use Dustin\ImpEx\Util\Value;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConversionNormalizer extends AbstractNormalizer
{
    /**
     * Context options.
     */
    public const ENABLE_MAX_DEPTH = 'enable_max_depth';

    public const DEPTH_KEY_PATTERN = 'depth_%s::%s';

    public const SKIP_NULL_VALUES = 'skip_null_values';

    public const MAX_DEPTH_HANDLER = 'max_depth_handler';

    public const DEEP_OBJECT_TO_POPULATE = 'deep_object_to_populate';

    public const PRESERVE_EMPTY_OBJECTS = 'preserve_empty_objects';

    public const CONVERTERS = 'converters';

    public const CONVERSION_CONTEXT = 'conversion_context';

    public const PROPERTY_ACCESSORS = 'property_accessors';

    /**
     * Access types.
     */
    public const ACCESS_READ = 'read';

    public const ACCESS_WRITE = 'write';

    /**
     * @var callable|null
     */
    protected $objectClassResolver = null;

    protected ?ClassDiscriminatorResolverInterface $classDiscriminatorResolver;

    public function __construct(
        ?ClassMetadataFactoryInterface $classMetadataFactory = null,
        ?NameConverterInterface $nameConverter = null,
        ?ClassDiscriminatorResolverInterface $classDiscriminatorResolver = null,
        ?callable $objectClassResolver = null,
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

    public function supportsNormalization(mixed $data, ?string $format = null)
    {
        return is_object($data);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null)
    {
        return class_exists($type) ||
            (interface_exists($type, false) && $this->classDiscriminatorResolver?->getMappingForClass($type) !== null);
    }

    /**
     * @return array|\ArrayObject
     *
     * @throws \RuntimeException
     * @throws ExtraAttributesException
     * @throws NotNormalizableValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws CircularReferenceException
     */
    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        $this->validateContext($context);

        if ($this->isCircularReference($object, $context)) {
            $result = $this->handleCircularReference($object, $format, $context);

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
            $attributeChildContext = $this->createAttributeChildContext($context, $attributeContext, $attribute, $format);
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
                $conversionContext = $this->createAttributeConversionContext($attribute, ConversionContext::NORMALIZATION, $object, null, $attributeChildContext);

                try {
                    $attributeValue = $converter->normalize($attributeValue, $conversionContext);
                } catch (AttributeConversionException $e) {
                    $conversionExceptions[] = $e;

                    continue;
                } catch (ErrorCode $errorCode) {
                    $conversionExceptions[] = AttributeConversionException::fromErrorCode(
                        $conversionContext->getPath(),
                        $conversionContext->getRootData(),
                        $errorCode
                    );

                    continue;
                } catch (\Throwable $th) {
                    $conversionExceptions[] = AttributeConversionException::fromException(
                        $conversionContext->getPath(),
                        $conversionContext->getRootData(),
                        $th
                    );

                    continue;
                }
            }

            if (!Value::isNormalized($attributeValue)) {
                if (!($this->serializer instanceof NormalizerInterface)) {
                    throw new \RuntimeException(sprintf("Cannot normalize attribute '%s'. Use a serializer or add a converter.", $attribute));
                }

                $attributeValue = $this->serializer->normalize($attributeValue, $format, $attributeChildContext);
            }

            if ($attributeValue === null && $this->skipNullValues($attributeContext)) {
                continue;
            }

            if ($this->nameConverter !== null) {
                $attribute = $this->nameConverter->normalize($attribute, $class, $format, $attributeContext);
            }

            $this->setValue($data, $attribute, $attributeValue, $attributeContext);
        }

        if (count($conversionExceptions) > 0) {
            $conversionContext = $this->getConversionContext($context);

            throw new AttributeConversionExceptionStack($conversionContext?->getPath() ?? '', $conversionContext?->getRootData() ?? Value::normalize($object), ...$conversionExceptions);
        }

        if ($this->preserveEmptyObjects($context) && empty($data)) {
            return new \ArrayObject();
        }

        return $data;
    }

    /**
     * @return object
     *
     * @throws \RuntimeException
     * @throws ExtraAttributesException
     * @throws NotNormalizableValueException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = [])
    {
        $this->validateContext($context);

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
            $attributeChildContext = $this->createAttributeChildContext($context, $attributeContext, $attribute, $format);

            $value = $this->fetchValue($data, $normalizedAttribute, $attributeContext);
            $value = $this->applyCallbacks($value, $class, $attribute, $format, $attributeContext);

            if ($converter = $this->getConverter($attribute, $attributeContext)) {
                $conversionContext = $this->createAttributeConversionContext($attribute, ConversionContext::DENORMALIZATION, $object, $data, $attributeChildContext);

                try {
                    $value = $converter->denormalize($value, $conversionContext);
                } catch (AttributeConversionException $e) {
                    $conversionExceptions[] = $e;

                    continue;
                } catch (ErrorCode $errorCode) {
                    $conversionExceptions[] = AttributeConversionException::fromErrorCode(
                        $conversionContext->getPath(),
                        $conversionContext->getRootData(),
                        $errorCode
                    );

                    continue;
                } catch (\Throwable $th) {
                    $conversionExceptions[] = AttributeConversionException::fromException(
                        $conversionContext->getPath(),
                        $conversionContext->getRootData(),
                        $th
                    );

                    continue;
                }
            }

            if ($this->deepObjectToPopulate($attributeContext)) {
                $deepObject = $this->getAttributeValue($object, $attribute, $format, $attributeContext);

                if (\is_object($deepObject)) {
                    if (!($this->serializer instanceof DenormalizerInterface)) {
                        throw new \RuntimeException(sprintf("Cannot denormalize deep object attribute '%s'. Use a serializer.", $attribute));
                    }

                    $attributeChildContext[self::OBJECT_TO_POPULATE] = $deepObject;
                    $value = $this->serializer->denormalize($value, $this->getObjectClass($deepObject), $format, $attributeChildContext);
                }
            }

            $this->setAttributeValue($object, $attribute, $value, $format, $attributeContext);
        }

        if (count($conversionExceptions) > 0) {
            $conversionContext = $this->getConversionContext($context);

            throw new AttributeConversionExceptionStack($conversionContext?->getPath() ?? '', $conversionContext?->getRootData() ?? $data, ...$conversionExceptions);
        }

        return $object;
    }

    protected function prepareForDenormalization(mixed $data): array
    {
        return ArrayUtil::ensure($data);
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
     * @return array|false
     */
    protected function getAllowedAttributes(string|object $classOrObject, array $context, bool $attributesAsString = false)
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
    protected function instantiateObject(array &$data, string $class, array &$context, \ReflectionClass $reflectionClass, array|bool $allowedAttributes, string $format = null)
    {
        if (($object = $this->extractObjectToPopulate($class, $context, self::OBJECT_TO_POPULATE)) !== null) {
            unset($context[self::OBJECT_TO_POPULATE]);

            return $object;
        }

        unset($context[self::OBJECT_TO_POPULATE]);

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

        return parent::instantiateObject($data, $class, $context, $reflectionClass, $allowedAttributes, $format);
    }

    /**
     *  @return array
     */
    protected function extractAttributes(object $object, string $format = null, array $context = [])
    {
        $attributes = [];
        $reflectionObject = new \ReflectionObject($object);

        foreach ($reflectionObject->getProperties() as $reflectionProperty) {
            if ($reflectionProperty->isStatic()) {
                continue;
            }

            $attributes[] = $reflectionProperty->getName();
        }

        return $attributes;
    }

    /**
     * @return mixed
     */
    protected function getAttributeValue(object $object, string $attribute, string $format = null, array $context = [])
    {
        $operation = $this->getAccess($attribute, $context, self::ACCESS_READ);

        return $operation->execute($object);
    }

    protected function setAttributeValue(object $object, string $attribute, $value, string $format = null, array $context = []): void
    {
        $operation = $this->getAccess($attribute, $context, self::ACCESS_WRITE);

        $operation->execute($object, $value);
    }

    /**
     * @return mixed
     */
    protected function fetchValue(array $data, string $attribute, array $context)
    {
        $operation = $this->getAccess($attribute, $context, self::ACCESS_READ);

        return $operation->execute($data);
    }

    protected function setValue(array &$data, string $attribute, mixed $value, array $context = []): void
    {
        $operation = $this->getAccess($attribute, $context, self::ACCESS_WRITE);

        $operation->execute($data, $value);
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

    protected function getAccess(string $attribute, array $context, string $access): AccessOperation
    {
        $operation = $context[self::PROPERTY_ACCESSORS][$attribute] ??
            $this->defaultContext[self::PROPERTY_ACCESSORS][$attribute] ??
            new AccessOperation([$attribute], $access === self::ACCESS_READ ? AccessOperation::GET : AccessOperation::SET);

        $isOperation = 'is'.ucfirst($access).'Operation';

        if (!AccessOperation::$isOperation($operation)) {
            throw new \LogicException(sprintf('Access must be %s-operation.', $access));
        }

        return $operation;
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

    protected function createAttributeConversionContext(string $attribute, string $direction, object $object, ?array $normalizedData, array $context): ConversionContext
    {
        $conversionContext = $this->getConversionContext($context);

        if ($conversionContext !== null) {
            return $conversionContext->subContext(new Path([$attribute]));
        }

        return new ConversionContext(
            $object,
            new Path([$attribute]),
            $attribute,
            $direction,
            $normalizedData,
            $context
        );
    }

    protected function createChildContext(array $parentContext, string $attribute, ?string $format): array
    {
        unset(
            $parentContext[self::ATTRIBUTES],
            $parentContext[self::IGNORED_ATTRIBUTES],
            $parentContext[self::CONVERTERS],
            $parentContext[self::PROPERTY_ACCESSORS],
            $parentContext[self::OBJECT_TO_POPULATE],
            $parentContext[self::CALLBACKS],
            $parentContext[self::DEFAULT_CONSTRUCTOR_ARGUMENTS]
        );

        return $parentContext;
    }

    protected function createAttributeChildContext(array $parentContext, array $attributeContext, string $attribute, ?string $format): array
    {
        $childContext = $this->createChildContext($parentContext, $attribute, $format);

        return array_merge($attributeContext, $childContext);
    }

    protected function getConversionContext(array $context): ?ConversionContext
    {
        return $context[self::CONVERSION_CONTEXT] ?? null;
    }

    private function validateContext(array $context)
    {
        $this->validateCallbackContext($context);

        if (isset($context[self::MAX_DEPTH_HANDLER]) && !\is_callable($context[self::MAX_DEPTH_HANDLER])) {
            throw new \InvalidArgumentException(sprintf("Context option '%s' must be callable.", self::MAX_DEPTH_HANDLER));
        }

        if (isset($context[self::CONVERTERS])) {
            if (!\is_array($context[self::CONVERTERS])) {
                throw new \InvalidArgumentException(sprintf("Context option '%s' must be array of converters.", self::CONVERTERS));
            }

            foreach ($context[self::CONVERTERS] as $attribute => $converter) {
                if (!$converter instanceof AttributeConverter) {
                    throw new \InvalidArgumentException(sprintf("Converter for attribute '%s' must be %s. Got '%s'.", $attribute, AttributeConverter::class, Type::getDebugType($converter)));
                }
            }
        }

        if (isset($context[self::PROPERTY_ACCESSORS])) {
            if (!is_array($context[self::PROPERTY_ACCESSORS])) {
                throw new \InvalidArgumentException(sprintf("Context option '%s' must be array of '%s'.", self::PROPERTY_ACCESSORS, AccessOperation::class));
            }

            foreach ($context[self::PROPERTY_ACCESSORS] as $attribute => $access) {
                if (!$access instanceof AccessOperation) {
                    throw new \InvalidArgumentException(sprintf("Accessor for attribute '%s' must be %s. Got '%s'.", $attribute, AccessOperation::class, Type::getDebugType($access)));
                }
            }
        }

        if (!$this->classMetadataFactory && isset($context[self::GROUPS]) && $context[self::GROUPS] != ['*']) {
            throw new \LogicException(sprintf('A %s must be set when selecting groups.', ClassMetadataFactoryInterface::class));
        }
    }
}
