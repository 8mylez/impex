<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Util\Type;

class NewLineToBreak extends BidirectionalConverter
{
    public const NO_XHTML = 'no_xhtml';

    public function normalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $this->validateStringConvertable($value, $context);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->removeBreaks($value);
        }

        return $this->newLineToBreak($value);
    }

    public function denormalize(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $this->validateStringConvertable($value, $context);

            $value = (string) $value;
        }

        $this->validateType($value, Type::STRING, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->newLineToBreak($value);
        }

        return $this->removeBreaks($value);
    }

    public function newLineToBreak(string $value): string
    {
        return nl2br($value, !$this->hasFlags(self::NO_XHTML));
    }

    public function removeBreaks(string $value): string
    {
        return preg_replace('/<br(\s)*(\/)?>/i', '', $value);
    }
}
