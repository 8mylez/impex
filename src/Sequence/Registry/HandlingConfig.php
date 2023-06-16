<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\RecordHandling;

class HandlingConfig implements PriorityInterface
{
    public function __construct(
        protected RecordHandling $handling,
        protected string $sequence,
        protected int $priority
    ) {
    }

    public function getHandling(): RecordHandling
    {
        return $this->handling;
    }

    public function getSequence(): string
    {
        return $this->sequence;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }
}
