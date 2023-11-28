<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Util\Type;

class EncodingConverter extends BidirectionalConverter
{
    public const CONVERT_ENCODING_FAILED_ERROR = 'IMPEX_CONVERSION__CONVERT_ENCODING_FAILED_ERROR';

    public const INVALID_ENCODING_ERROR = 'IMPEX_CONVERSION__INVALID_ENCODING_ERROR';

    public function __construct(
        private string $normalizedEncoding,
        private ?string $internalEncoding = null,
        string ...$flags
    ) {
        parent::__construct(...$flags);
    }

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

        try {
            $result = \mb_convert_encoding($value, $this->normalizedEncoding, $this->internalEncoding);
        } catch (\ValueError $error) {
            $encoding = implode(', ', [$this->normalizedEncoding, $this->internalEncoding]);

            throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'One of the encodings [{{ encoding }}] seems not to be valid.', ['encoding' => $encoding], self::INVALID_ENCODING_ERROR);
        }

        if ($result === false) {
            throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'Encoding could not be converted.', [], self::CONVERT_ENCODING_FAILED_ERROR);
        }

        return $result;
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

        try {
            $result = \mb_convert_encoding($value, $this->internalEncoding ?? mb_internal_encoding(), $this->normalizedEncoding);
        } catch (\ValueError $error) {
            $encoding = implode(', ', [$this->normalizedEncoding, $this->internalEncoding]);

            throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'One of the encodings [{{ encoding }}] seems not to be valid.', ['encoding' => $encoding], self::INVALID_ENCODING_ERROR);
        }

        if ($result === false) {
            throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'Encoding could not be converted.', [], self::CONVERT_ENCODING_FAILED_ERROR);
        }

        return $result;
    }
}
