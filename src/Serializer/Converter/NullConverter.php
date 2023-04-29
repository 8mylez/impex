<?php

namespace Dustin\ImpEx\Serializer\Converter;

use Dustin\Encapsulation\EncapsulationInterface;
use Dustin\ImpEx\Util\Type;

/**
 * Converts empty values to null.
 */
class NullConverter extends UnidirectionalConverter
{
    public const ALLOW_NUMERIC = 'allow_numeric';

    public const ALLOW_BOOL = 'allow_bool';

    public const ALLOW_STRING = 'allow_string';

    public const ALLOW_ARRAY = 'allow_array';

    public const ALLOW_ZERO_STRING = 'allow_zero_string';

    public static function getAvailableFlags(): array
    {
        return [
            self::ALLOW_NUMERIC,
            self::ALLOW_BOOL,
            self::ALLOW_STRING,
            self::ALLOW_ARRAY,
            self::ALLOW_ZERO_STRING,
        ];
    }

    public function convert($value, EncapsulationInterface $object, string $path, string $attributeName, ?array $data = null)
    {
        if (!empty($value)) {
            return $value;
        }

        $type = Type::getType($value);

        switch ($type) {
            case Type::INT:
            case Type::FLOAT:
                return $this->hasFlag(self::ALLOW_NUMERIC) ? $value : null;
            case Type::BOOL:
                return $this->hasFlag(self::ALLOW_BOOL) ? $value : null;
            case Type::STRING:
                if ($this->hasFlag(self::ALLOW_STRING)) {
                    return $value;
                }

                return $this->hasFlag(self::ALLOW_ZERO_STRING) && \is_numeric($value) ? $value : null;
            case Type::ARRAY:
                return $this->hasFlag(self::ALLOW_ARRAY) ? $value : null;
            default:
                return null;
        }
    }
}
