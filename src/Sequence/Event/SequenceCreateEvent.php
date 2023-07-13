<?php

namespace Dustin\ImpEx\Sequence\Event;

use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;
use Symfony\Contracts\EventDispatcher\Event;

class SequenceCreateEvent extends Event
{
    public function __construct(
        private SequenceDefinition $definition
    ) {
    }

    public function getDefinition(): SequenceDefinition
    {
        return $this->definition;
    }
}
