<?php

namespace Dustin\ImpEx\Sequence\Registry\Config;

use Dustin\Encapsulation\Container;
use Dustin\ImpEx\Sequence\Registry\SequenceDefinition;

class SequenceDefinitionContainer extends Container
{
    protected function getAllowedClass(): ?string
    {
        return SequenceDefinition::class;
    }
}
