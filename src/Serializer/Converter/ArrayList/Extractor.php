<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class Extractor extends UnidirectionalConverter
{
    public const MODE_EXCLUDE = 'exclude';

    public const MODE_INCLUDE = 'include';

    public function __construct(private array $keys, private string $mode = self::MODE_INCLUDE, string ...$flags)
    {
        if ($mode !== self::MODE_EXCLUDE && $mode !== self::MODE_INCLUDE) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid mode.', $mode));
        }

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

        if ($this->mode === self::MODE_INCLUDE) {
            return array_intersect_key($value, array_flip($this->keys));
        }

        return array_diff_key($value, array_flip($this->keys));
    }
}
