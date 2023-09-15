<?php

namespace Dustin\ImpEx\PropertyAccess;

class Field
{
    public const OPERATOR_PUSH = '<push>';

    public static function isField(string $field): bool
    {
        return $field !== self::OPERATOR_PUSH;
    }
}
