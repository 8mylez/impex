<?php

namespace Dustin\ImpEx\Sequence\Registry\Config;

use Dustin\Encapsulation\Container;

class SequenceDefinitionContainer extends Container
{
    protected function getAllowedClass(): ?string
    {
        return SequenceDefinition::class;
    }
}
