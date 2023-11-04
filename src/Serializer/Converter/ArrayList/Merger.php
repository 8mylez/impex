<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class Merger extends UnidirectionalConverter
{
    /**
     * @var array|callable
     */
    private $data;

    public function __construct(array|callable $data, string ...$flags)
    {
        $this->data = $data;

        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $value = ArrayUtil::ensure($value);
        }

        $this->validateType($value, Type::ARRAY, $context);

        $mergeData = $this->data;

        if (is_callable($mergeData)) {
            $mergeData = $mergeData($value, $context);
        }

        return array_merge($value, $mergeData);
    }
}
