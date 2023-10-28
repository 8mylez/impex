<?php

namespace Dustin\ImpEx\Serializer\Converter\Condition;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\PropertyAccess\PropertyAccessor;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;

class HasProperty extends Condition
{
    /**
     * @var Path
     */
    private $path;

    public function __construct(string|array|Path $path)
    {
        if (!$path instanceof Path) {
            $path = new Path($path);
        }

        $this->path = $path;
    }

    public function isFullfilled(mixed $value, ConversionContext $context): bool
    {
        return PropertyAccessor::has($this->path, $value);
    }
}
