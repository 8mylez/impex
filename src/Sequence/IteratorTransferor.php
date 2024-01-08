<?php

namespace Dustin\ImpEx\Sequence;

class IteratorTransferor implements Transferor
{
    public function __construct(private iterable $iterator)
    {
    }

    public function passRecords(): \Generator
    {
        yield from $this->iterator;
    }
}
