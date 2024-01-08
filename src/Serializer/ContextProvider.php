<?php

namespace Dustin\ImpEx\Serializer;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class ContextProvider implements ContextProviderInterface
{
    public function __construct(private array $context)
    {
    }

    public function getContext(ConversionContext $context): array
    {
        return $this->context;
    }
}
