<?php

namespace Dustin\ImpEx\Serializer\Converter;

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

    public function convert(mixed $value, ConversionContext $context): mixed
    {
        if (!empty($value)) {
            return $value;
        }

        $type = Type::getType($value);

        switch ($type) {
            case Type::INT:
            case Type::FLOAT:
                return $this->hasFlags(self::ALLOW_NUMERIC) ? $value : null;
            case Type::BOOL:
                return $this->hasFlags(self::ALLOW_BOOL) ? $value : null;
            case Type::STRING:
                if ($this->hasFlags(self::ALLOW_STRING)) {
                    return $value;
                }

                return $this->hasFlags(self::ALLOW_ZERO_STRING) && \is_numeric($value) ? $value : null;
            case Type::ARRAY:
                return $this->hasFlags(self::ALLOW_ARRAY) ? $value : null;
            default:
                return null;
        }
    }
}
