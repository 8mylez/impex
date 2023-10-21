<?php

namespace Dustin\ImpEx\Serializer\Converter\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\PropertyAccessor;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;

class Collector extends UnidirectionalConverter
{
    public function __construct(private array $paths, string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        $result = [];

        foreach ($this->paths as $path) {
            $collected = PropertyAccessor::collect($path, $value, ...$this->flags);

            $result = array_merge_recursive($result, $collected);
        }

        return $result;
    }
}
