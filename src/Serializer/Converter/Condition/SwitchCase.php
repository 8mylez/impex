<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

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

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        foreach ($this->cases as $key => $case) {
            if ($case->isFullfilled($value, $context)) {
                return $case->getConverter()->normalize($value, $context->subContext(new Path([sprintf('case#%s', $key)])));
            }
        }

        if ($this->defaultConverter !== null) {
            return $this->defaultConverter->normalize($value, $context->subContext(new Path(['case#<default>'])));
        }

        return $value;
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        foreach ($this->cases as $key => $case) {
            if ($case->isFullfilled($value, $context)) {
                return $case->getConverter()->denormalize($value, $context->subContext(new Path([sprintf('case#%s', $key)])));
            }
        }

        if ($this->defaultConverter !== null) {
            return $this->defaultConverter->denormalize($value, $context->subContext(new Path(['case#<default>'])));
        }

        return $value;
    }
}
