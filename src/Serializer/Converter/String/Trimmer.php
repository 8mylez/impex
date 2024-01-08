<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Trimmer extends UnidirectionalConverter
{
    public const MODE_LEFT = 'left';

    public const MODE_RIGHT = 'right';

    public const MODE_BOTH = 'both';

    public function __construct(private string $mode = self::MODE_BOTH, private ?string $characters = null, string ...$flags)
    {
        if (!in_array($mode, [self::MODE_LEFT, self::MODE_RIGHT, self::MODE_BOTH])) {
            throw new \InvalidArgumentException(sprintf('%s is not a valid mode.', $mode));
        }

        if ($characters === null) {
            $this->characters = " \n\r\t\v\x00";
        }

        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);

        switch ($this->mode) {
            case self::MODE_BOTH:
                return trim($value, $this->characters);
            case self::MODE_LEFT:
                return ltrim($value, $this->characters);
            case self::MODE_RIGHT:
                return rtrim($value, $this->characters);
        }
    }
}
