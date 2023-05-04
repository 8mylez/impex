<?php

namespace Dustin\ImpEx\Sequence;

abstract class AbstractSequence implements RecordHandling
{
    protected ?Transferor $transferor = null;

    protected array $handlers = [];

    final public function __construct(RecordHandling ...$handlers)
    {
        $this->handlers = $handlers;
    }

    protected function setTransferor(?Transferor $transferor)
    {
        $this->transferor = $transferor;
    }
}
