<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\PropertyAccess\Path;

class AttributeConverterSequence extends BidirectionalConverter
{
    public const UNIDIRECTIONAL = 'unidirectional';

    /**
     * @var AttributeConverter[]
     */
    private $converters = [];

    /**
     * @param AttributeConverter[] $converters
     */
    public function __construct(array $converters, string ...$flags)
    {
        foreach ($converters as $key => $converter) {
            $this->setConverter($key, $converter);
        }

        parent::__construct(...$flags);
    }

    public function setConverter(int|string $key, AttributeConverter $converter): void
    {
        $this->converters[$key] = $converter;
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        $steps = 0;
        foreach ($this->converters as $key => $converter) {
            $path = sprintf('[step#%s]', $steps++);

            if (!is_int($key)) {
                $path .= sprintf(':<%s>', $key);
            }

            $subContext = $context->subContext(new Path([$path]));
            $value = $converter->normalize($value, $subContext);
        }

        return $value;
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        $converters = $this->converters;

        if (!$this->hasFlags(self::UNIDIRECTIONAL)) {
            $converters = array_reverse($converters, true);
        }

        $steps = 0;
        foreach ($converters as $key => $converter) {
            $path = sprintf('[step#%s]', $steps++);

            if (!is_int($key)) {
                $path .= sprintf(':<%s>', $key);
            }

            $subContext = $context->subContext(new Path([$path]));
            $value = $converter->denormalize($value, $subContext);
        }

        return $value;
    }
}
