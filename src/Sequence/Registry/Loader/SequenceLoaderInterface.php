<?php

namespace Dustin\ImpEx\Sequence\Registry\Loader;

use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinitionContainer;

interface SequenceLoaderInterface
{
    public function load(): SequenceDefinitionContainer;
}
