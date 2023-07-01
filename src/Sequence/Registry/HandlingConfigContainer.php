<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\Container;

class HandlingConfigContainer extends Container
{
    protected function getAllowedClass(): ?string
    {
        return HandlingConfig::class;
    }
}
