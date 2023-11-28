<?php

namespace Dustin\ImpEx\Serializer\Converter;

class Callback extends BidirectionalConverter
{
    /**
     * @var callable
     */
    private $normalizationCallback;

    /**
     * @var callable
     */
    private $denormalizationCallback;

    public function __construct(
        callable $normalizationCallback,
        ?callable $denormalizationCallback = null
    ) {
        $this->normalizationCallback = $normalizationCallback;

        if ($denormalizationCallback === null) {
            $this->denormalizationCallback = $normalizationCallback;
        } else {
            $this->denormalizationCallback = $denormalizationCallback;
        }
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        $c = $this->normalizationCallback;

        return $c($value, $context);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        $c = $this->denormalizationCallback;

        return $c($value, $context);
    }
}
