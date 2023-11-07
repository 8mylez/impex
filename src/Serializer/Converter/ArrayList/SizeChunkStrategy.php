<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionException;
use Dustin\ImpEx\Serializer\Exception\AttributeConversionExceptionStack;

class SizeChunkStrategy extends ChunkStrategy
{
    /** flags */
    public const STRICT_CHUNK_SIZE = 'strict_chunk_size';

    /** error codes */
    public const INVALID_ARRAY_SIZE_ERROR = 'IMPEX_CONVERSION__INVALID_ARRAY_SIZE_ERROR';

    public const EMPTY_ARRAY_ERROR = 'IMPEX_CONVERSION__EMPTY_ARRAY_ERROR';

    public function __construct(
        private int $chunkSize,
        private bool $preserveKeys = false,
        private bool $strictChunkSize = false
    ) {
        if ($chunkSize <= 0) {
            throw new \InvalidArgumentException('Chunk size must be greater than zero.');
        }
    }

    public function chunk(array $data, ConversionContext $context): array
    {
        return array_chunk($data, $this->chunkSize, $this->preserveKeys);
    }

    public function merge(array $arrays, ConversionContext $context): array
    {
        $this->validateArrays($arrays, $context);

        if ($this->strictChunkSize === true) {
            $exceptions = new AttributeConversionExceptionStack($context->getPath(), $context->getRootData());
            $i = 0;

            foreach ($arrays as $key => $a) {
                $subContext = $context->subContext(new Path([$key]));
                ++$i;

                try {
                    $this->validateChunkSize($a, $subContext, $i === count($arrays));
                } catch (AttributeConversionException $e) {
                    $exceptions->add($e);
                }
            }

            $exceptions->throw();
        }

        return array_merge(...$arrays);
    }

    protected function validateChunkSize(array $array, ConversionContext $context, bool $isLast): void
    {
        if (empty($array)) {
            throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'Array must not be empty.', [], self::EMPTY_ARRAY_ERROR);
        }

        $size = count($array);

        if (
            ($isLast === false && $size !== $this->chunkSize) ||
            ($isLast === true && $size > $this->chunkSize)
        ) {
            throw new AttributeConversionException($context->getPath(), $context->getRootData(), 'Array size must be {{ expectedSize }}. {{ actualSize }} elements were found.', ['expectedSize' => $this->chunkSize, 'actualSize' => $size], self::INVALID_ARRAY_SIZE_ERROR);
        }
    }
}
