<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class ConditionalConverter extends BidirectionalConverter
{
    public function __construct(
        private Condition $condition,
        private AttributeConverter $fulfilledConverter,
        private ?AttributeConverter $unfulfilledConverter = null
    ) {
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->condition->isFullfilled($value, $context)) {
            return $this->fulfilledConverter->normalize($value, $context->subContext(new Path(['step#<true>'])));
        }

        if ($this->unfulfilledConverter !== null) {
            return $this->unfulfilledConverter->normalize($value, $context->subContext(new Path(['step#<false>'])));
        }

        return $value;
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->condition->isFullfilled($value, $context)) {
            return $this->fulfilledConverter->denormalize($value, $context->subContext(new Path(['step#<true>'])));
        }

        if ($this->unfulfilledConverter !== null) {
            return $this->unfulfilledConverter->denormalize($value, $context->subContext(new Path(['step#<false>'])));
        }

        return $value;
    }
}
