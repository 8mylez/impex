<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\AttributeConverter;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class ListConverter extends BidirectionalConverter
{
    public function __construct(
        private AttributeConverter $converter,
        string ...$flags
    ) {
        parent::__construct(...$flags);
    }

    public function normalize(mixed $data, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $data = ArrayUtil::cast($data);
        }

        $this->validateType($data, Type::ARRAY, $context);

        $converted = [];
        $exceptions = [];

        foreach ($data as $key => $value) {
            try {
                $converted[$key] = $this->converter->normalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionException $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($context->getPath(), $context->getRootData(), ...$exceptions);
        }

        return $converted;
    }

    public function denormalize(mixed $data, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $data === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $data = ArrayUtil::cast($data);
        }

        $this->validateType($data, Type::ARRAY, $context);

        $converted = [];
        $exceptions = [];

        foreach ($data as $key => $value) {
            try {
                $converted[$key] = $this->converter->denormalize($value, $context->subContext(new Path([$key])));
            } catch (AttributeConversionException $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($context->getPath(), $context->getRootData(), ...$exceptions);
        }

        return $converted;
    }
}
