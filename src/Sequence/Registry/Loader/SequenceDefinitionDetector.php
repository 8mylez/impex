<?php

namespace Dustin\ImpEx\Sequence\Registry\Loader;

class SequenceDefinitionDetector
{
    public static function isSequenceDefinition(array $data): bool
    {
        foreach (['id', 'class', 'sections'] as $requiredField) {
            if (!array_key_exists($requiredField, $data)) {
                return false;
            }
        }

        return true;
    }
}
