<?php

namespace Dustin\ImpEx\Serializer\Converter\String;

use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Util\Type;

class Hasher extends UnidirectionalConverter
{
    public const BINARY = 'binary';

    public const INVALID_ALGORITHM_ERROR = 'IMPEX_CONVERSION__INVALID_ALGORITHM_ERROR';

    public function __construct(private string $algorithm, private array $options = [], string ...$flags)
    {
        parent::__construct(...$flags);
    }

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        $value = $this->ensureType($value, Type::STRING, $context);

        try {
            return hash($this->algorithm, $value, $this->hasFlags(self::BINARY), $this->options);
        } catch (\ValueError $error) {
            throw new AttributeConversionException($context->getPath(), $context->getRootData(), '{{ algorithm }} is not a valid algorithm.', ['algorithm' => $this->algorithm], self::INVALID_ALGORITHM_ERROR);
        }
    }
}
