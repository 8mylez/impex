<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
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

    public function normalize($value, EncapsulationInterface $object, string $path, string $attributeName)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return $value;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $value = ArrayUtil::cast($value);
        }

        $this->validateType($value, Type::ARRAY, $path, $object->toArray());

        if ($this->hasFlag(self::REVERSE)) {
            $this->validateArrays($value, $path, $object->toArray());

            return $this->merge(...$value);
        }

        return $this->chunk($value);
    }

    public function denormalize($value, EncapsulationInterface $object, string $path, string $attributeName, array $data)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $value = ArrayUtil::cast($value);
        }

        $this->validateType($value, Type::ARRAY, $path, $data);

        if ($this->hasFlag(self::REVERSE)) {
            return $this->chunk($value);
        }

        $this->validateArrays($value, $path, $data);

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

    private function validateArrays(array $arrays, string $path, array $data): void
    {
        $exceptions = [];
        $i = 0;

        foreach ($arrays as $key => $value) {
            $subPath = $path.'/'.$key;
            ++$i;
            try {
                $this->validateType($value, Type::ARRAY, $subPath, $data);

                if ($this->hasFlag(self::STRICT_CHUNK_SIZE)) {
                    $this->validateChunkSize($value, $subPath, $data, $i === count($arrays));
                }
            } catch (AttributeConversionException $e) {
                $exceptions[] = $e;
            }
        }

        if (count($exceptions) > 0) {
            throw new AttributeConversionExceptionStack($path, $data, ...$exceptions);
        }
    }

    private function validateChunkSize(array $array, string $path, array $data, bool $isLast)
    {
        if (empty($array)) {
            throw new InvalidArrayException($path, $data, 'Array must not be empty', []);
        }

        $size = count($array);

        if (
            ($isLast === false && $size !== $this->chunkSize) ||
            ($isLast === true && $size > $this->chunkSize)
        ) {
            throw new InvalidArrayException($path, $data, 'Array size must match chunk size of {{ chunkSize }}. {{ elementCount }} elements found.', ['chunkSize' => $this->chunkSize, 'elementCount' => $size]);
        }
    }
}
