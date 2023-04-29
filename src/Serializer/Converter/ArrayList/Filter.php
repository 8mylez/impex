<?php

namespace Dustin\ImpEx\Serializer\Converter\ArrayList;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Serializer\Converter\UnidirectionalConverter;
use Dustin\ImpEx\Util\Type;

class Filter extends UnidirectionalConverter
{
    /**
     * @var callable|null
     */
    private $callback = null;

    public function __construct(?callable $callback = null, string ...$flags)
    {
        $this->callback = $callback;

        parent::__construct(...$flags);
    }

    public static function getAvailableFlags(): array
    {
        return [
            self::SKIP_NULL,
            self::STRICT,
            self::REINDEX,
        ];
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $normalizedData = null)
    {
        if ($this->hasFlag(self::SKIP_NULL) && $value === null) {
            return null;
        }

        if (!$this->hasFlag(self::STRICT)) {
            $value = (array) $value;
        }

        $this->validateType($value, Type::ARRAY, $path, $normalizedData ?? $object->toArray());

        $value = $this->callback !== null ? array_filter($value, $this->callback) : array_filter($value);

        if ($this->hasFlag(self::REINDEX)) {
            $value = array_values($value);
        }

        return $value;
    }
}
