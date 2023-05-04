<?php

namespace Dustin\ImpEx\Sequence\Exception;

class MultipleFactoryException extends \Exception
{
    public function __construct(string $sequence, string $factoryClass, string $existingFactoryClass)
    {
        parent::__construct(
            sprintf(
                "Multiple factories for sequence '%s'! Tried to add %s but %s is already registered!",
                $sequence, $factoryClass, $existingFactoryClass
            )
        );
    }
}
