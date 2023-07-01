<?php

namespace Dustin\ImpEx\Sequence\Event;

use Dustin\ImpEx\Sequence\Registry\HandlingConfigContainer;
use Dustin\ImpEx\Sequence\Registry\SequenceConfig;
use Dustin\ImpEx\Sequence\Registry\SequenceConfigContainer;
use Symfony\Contracts\EventDispatcher\Event;

class SequenceCreateEvent extends Event
{
    public function __construct(
        private HandlingConfigContainer $recordHandlers,
        private SequenceConfigContainer $subSequences,
        private SequenceConfig $config
    ) {
    }

    public function getRecordHandlers(): HandlingConfigContainer
    {
        return $this->recordHandlers;
    }

    public function setRecordHandlers(HandlingConfigContainer $recordHandlers): void
    {
        $this->recordHandlers = $recordHandlers;
    }

    public function getSubSequences(): SequenceConfigContainer
    {
        return $this->subSequences;
    }

    public function setSubSequences(SequenceConfigContainer $subSequences): void
    {
        $this->subSequences = $subSequences;
    }

    public function getConfig(): SequenceConfig
    {
        return $this->config;
    }
}
