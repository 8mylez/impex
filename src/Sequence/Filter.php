<?php

namespace Dustin\ImpEx\Sequence;

abstract class Filter implements RecordHandling, Transferor
{
    protected ?Transferor $transferor = null;

    /**
     * @param mixed $record
     */
    abstract public function filter($record): bool;

    public function handle(Transferor $transferor): void
    {
        $this->transferor = $transferor;
    }

    public function passRecords(): \Generator
    {
        if ($this->transferor === null) {
            return;
        }

        /** @var mixed $record */
        foreach ($this->transferor->passRecords() as $record) {
            if ($this->filter($record)) {
                yield $record;
            }
        }
    }
}
