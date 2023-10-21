<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;
use Dustin\ImpEx\Serializer\Exception\InvalidArrayException;
use Dustin\ImpEx\Util\ArrayUtil;
use Dustin\ImpEx\Util\Type;

class Chunker extends BidirectionalConverter
{
    public const STRICT_CHUNK_SIZE = 'strict_chunk_size';

    public function __construct(
        private int $chunkSize,
        private bool $preserveKeys = false,
        string ...$flags
    ) {
        if ($chunkSize <= 0) {
            throw new \InvalidArgumentException('Chunk size must be greater than zero.');
        }

        parent::__construct(...$flags);
    }

    public function normalize(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return $value;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $value = ArrayUtil::ensure($value);
        }

        $this->validateType($value, Type::ARRAY, $context);

        if ($this->hasFlags(self::REVERSE)) {
            $this->validateArrays($value, $context);

            return $this->merge(...$value);
        }

        return $this->chunk($value);
    }

    public function denormalize(mixed $value, ConversionContext $context): array|null
    {
        if ($this->hasFlags(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlags(self::STRICT)) {
            $value = ArrayUtil::ensure($value);
        }

        $this->validateType($value, Type::ARRAY, $context);

        if ($this->hasFlags(self::REVERSE)) {
            return $this->chunk($value);
        }

        $this->validateArrays($value, $context);

        return $this->merge(...$value);
    }

    public function chunk(array $data): array
    {
        return array_chunk($data, $this->chunkSize, $this->preserveKeys);
    }

    public function merge(array ...$arrays): array
    {
        return array_merge(...$arrays);
    }

    private function validateArrays(array $arrays, ConversionContext $context): void
    {
        $exceptions = [];
        $i = 0;

        foreach ($arrays as $key => $value) {
            $subContext = $context->subContext(new Path([$key]));

            ++$i;
            try {
                $this->validateType($value, Type::ARRAY, $subContext);

                if ($this->hasFlags(self::STRICT_CHUNK_SIZE)) {
                    $this->validateChunkSize($value, $subContext, $i === count($arrays));
                }
            } catch (AttributeConversionException $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($context->getPath(), $context->getRootData(), ...$exceptions);
        }
    }

    private function validateChunkSize(array $array, ConversionContext $context, bool $isLast): void
    {
        if (empty($array)) {
            throw new InvalidArrayException($context->getPath(), $context->getRootData(), 'Array must not be empty', []);
        }

        $size = count($array);

        if (
            ($isLast === false && $size !== $this->chunkSize) ||
            ($isLast === true && $size > $this->chunkSize)
        ) {
            throw new InvalidArrayException($context->getPath(), $context->getRootData(), 'Array size must match chunk size of {{ chunkSize }}. {{ elementCount }} elements found.', ['chunkSize' => $this->chunkSize, 'elementCount' => $size]);
        }
    }
}
