<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\AbstractSequence;
use Dustin\ImpEx\Sequence\RecordHandling;

interface SequenceFactoryInterface
{
    /**
     * @param RecordHandling[]   $recordHandlers
     * @param AbstractSequence[] $subSequences
     */
    public function build(SequenceConfig $config, array $recordHandlers, array $subSequences, RecordHandlingRegistry $registry): AbstractSequence;
}
