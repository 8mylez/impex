<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class ExtractionByKeyExclusion extends ArrayExtractionStrategy
{
    /**
     * @var array
     */
    private $keys;

    public function __construct(array $keys)
    {
        $keys = array_values($keys);

        foreach ($keys as $i => $key) {
            if (is_object($key) || !Type::isStringConvertable(Type::getType($key))) {
                throw new \InvalidArgumentException(sprintf('Element of type %s at %s is not a valid array key.', Type::getDebugType($key), $i));
            }
        }

        $this->keys = $keys;
    }

    public function extract(array $data, ConversionContext $context): mixed
    {
        return array_diff_key($data, array_flip($this->keys));
    }
}
