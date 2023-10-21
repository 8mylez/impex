<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class Filter extends UnidirectionalConverter
{
    /**
     * @var callable|null
     */
    private $callback = null;

    public function __construct(?\Closure $callback = null, private int $mode = 0, string ...$flags)
    {
        $this->callback = $callback;

        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $value = ArrayUtil::ensure($value);
        }

        $this->validateType($value, Type::ARRAY, $context);

        $value = array_filter($value, $this->callback, $this->mode);

        if ($this->hasFlags(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
