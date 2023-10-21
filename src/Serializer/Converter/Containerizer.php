<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\Container;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class Containerizer extends BidirectionalConverter
{
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

        $container->add(...$value);

        return $container;
    }
}
