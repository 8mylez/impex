<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\Sequence;

interface SequenceFactoryInterface
{
    public function build(SequenceDefinition $definition): Sequence;
}
