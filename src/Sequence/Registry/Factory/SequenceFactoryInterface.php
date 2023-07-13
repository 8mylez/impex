<?php

namespace Dustin\ImpEx\Sequence\Registry\Factory;

use Dustin\ImpEx\Sequence\Registry\SequenceDefinition;
use Dustin\ImpEx\Sequence\Sequence;

interface SequenceFactoryInterface
{
    public function build(SequenceDefinition $definition): Sequence;
}
