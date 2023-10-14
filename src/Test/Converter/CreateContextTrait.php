<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

trait CreateContextTrait
{
    protected function createConversionContext(string $direction): ConversionContext
    {
        return new ConversionContext(
            new \stdClass(),
            new Path(['someAttribute']),
            'someAttribute',
            $direction,
            [],
            []
        );
    }
}
