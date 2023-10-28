<?php

namespace Dustin\ImpEx\Serializer\Converter\Encapsulation;

use Dustin\Encapsulation\Container;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class Containerizer extends BidirectionalConverter
{
    public const INVALID_CONTAINER_ELEMENT_TYPE_ERROR = 'IMPEX_CONVERSION__INVALID_CONTAINER_ELEMENT_ERROR';

    public function __construct(private string $containerClass = Container::class, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $this->validateType($value, Container::class, $context);

        return $value->toArray();
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $value = ArrayUtil::ensure($value);
        }

        $this->validateType($value, Type::ARRAY, $context);

        $containerClass = $this->containerClass;
        $container = new $containerClass();

        foreach ($value as $v) {
            try {
                $container->add($v);
            } catch (\InvalidArgumentException $exception) {
                throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'Container of class {{ containerClass }} cannot hold element of type {{ type }}.', ['containerClass' => $containerClass, 'type' => Type::getDebugType($v)], self::INVALID_CONTAINER_ELEMENT_TYPE_ERROR);
            }
        }

        return $container;
    }
}
