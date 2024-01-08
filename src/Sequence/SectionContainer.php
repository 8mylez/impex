<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\Container;

class SectionContainer extends Container
{
    protected function getAllowedClass(): ?string
    {
        return Section::class;
    }
}
