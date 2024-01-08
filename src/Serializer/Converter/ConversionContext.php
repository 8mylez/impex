<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Util\Value;

class ConversionContext
{
    public const NORMALIZATION = 'normalization';

    public const DENORMALIZATION = 'denormalization';

    private array $rootData = [];

    public function __construct(
        private object $object,
        private Path $path,
        private string $attributeName,
        private string $direction,
        private ?array $normalizedData = [],
        private array $normalizationContext = []
    ) {
        $this->rootData = Value::normalize($normalizedData ?? $object);
    }

    public function getObject(): object
    {
        return $this->object;
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    public function getNormalizedData(): ?array
    {
        return $this->normalizedData;
    }

    public function getNormalizationContext(): array
    {
        return $this->normalizationContext;
    }

    public function getRootData(): array
    {
        return $this->rootData;
    }

    public function subContext(Path $appendPath): self
    {
        return new self(
            $this->object,
            $this->path->merge($appendPath),
            $this->attributeName,
            $this->direction,
            $this->normalizedData,
            $this->normalizationContext
        );
    }
}
