<?php

namespace Dustin\ImpEx\Sequence;

abstract class Filter extends DirectPass
{
    /**
     * @param mixed $record
     */
    abstract public function filter($record): bool;

    public function passFrom(Transferor $transferor): \Generator
    {
        foreach ($transferor->passRecords() as $record) {
            if ($this->filter($record)) {
                yield $record;
            }
        }
    }
}
