<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\RecordHandling;

class HandlingConfig implements PriorityInterface
{
    protected RecordHandling $handling;

    protected string $sequence;

    protected int $priority;

    public function __construct(
        RecordHandling $handling, string $sequence, int $priority
    ) {
        $this->handling = $handling;
        $this->sequence = $sequence;
        $this->priority = $priority;
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
