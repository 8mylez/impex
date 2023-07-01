<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\Container;

class SequenceConfigContainer extends Container
{
    protected function getAllowedClass(): ?string
    {
        return SequenceConfig::class;
    }
}
