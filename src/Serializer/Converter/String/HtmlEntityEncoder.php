<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class HtmlEntityEncoder extends BidirectionalConverter
{
    public const SPECIAL_CHARS_ONLY = 'special_chars_only';

    public const DISABLE_DOUBLE_ENCODE = 'disable_double_encode';

    public function __construct(private ?int $bitFlags = null, string ...$flags)
    {
        if ($bitFlags === null) {
            $this->bitFlags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;
        }

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->decode($value);
        }

        return $this->encode($value);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->encode($value);
        }

        return $this->decode($value);
    }

    public function encode(string $value): string
    {
        if ($this->hasFlags(self::SPECIAL_CHARS_ONLY)) {
            return htmlspecialchars(
                $value,
                $this->bitFlags,
                null,
                !$this->hasFlags(self::DISABLE_DOUBLE_ENCODE)
            );
        }

        return htmlentities(
            $value,
            $this->bitFlags,
            null,
            !$this->hasFlags(self::DISABLE_DOUBLE_ENCODE)
        );
    }

    public function decode(string $value): string
    {
        if ($this->hasFlags(self::SPECIAL_CHARS_ONLY)) {
            return htmlspecialchars_decode($value, $this->bitFlags);
        }

        return html_entity_decode($value, $this->bitFlags);
    }
}
