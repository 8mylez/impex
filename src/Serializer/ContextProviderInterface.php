<?php

namespace Dustin\ImpEx\Serializer;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;

interface ContextProviderInterface
{
    public function getContext(ConversionContext $context): array;
}
