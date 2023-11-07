<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\InvalidTypeException;
use Dustin\ImpEx\Util\Type;

class ArrayFillStrategy extends ArrayConversionStrategy
{
    public const VALUE = 'value';

    public const PREDEFINED = 'predefined';

    public function __construct(
        private ?array $keys = null,
        private mixed $value = null,
        private string $keyMode = self::PREDEFINED,
        private string $valueMode = self::VALUE
    ) {
        if ($keyMode === self::PREDEFINED && empty($keys)) {
            throw new \LogicException('Keys cannot be empty in key mode "predefined".');
        }
    }

    public function convert(mixed $value, ConversionContext $context): array
    {
        $keys = $this->keyMode === self::PREDEFINED ? $this->keys : $value;

        $this->validateKeys($keys, $context);

        $fillValue = $this->valueMode === self::PREDEFINED ? $this->value : $value;

        return array_fill_keys($keys, $fillValue);
    }

    protected function validateKeys(array $data, ConversionContext $context): void
    {
        $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());

        foreach ($data as $key => $value) {
            if (!Type::isStringConvertable(Type::getType($value))) {
                $subContext = $context->subContext(new Path([$key]));
                $exceptions->add(InvalidTypeException::invalidArrayKey($value, $subContext));
            }
        }

        $exceptions->throw();
    }
}
