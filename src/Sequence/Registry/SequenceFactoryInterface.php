<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\AbstractSequence;

interface SequenceFactoryInterface
{
    public function build(SequenceConfig $config, HandlingConfigContainer $recordHandlers, SequenceConfigContainer $subSequences): AbstractSequence;
}
