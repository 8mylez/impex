<?php

namespace Dustin\ImpEx\Sequence;

abstract class DirectPass implements RecordHandling, Transferor
{
    /**
     * @var Transferor
     */
    private $transferor = null;

    abstract public function passFrom(Transferor $transferor): \Generator;

    public function handle(Transferor $transferor): void
    {
        $this->transferor = $transferor;
    }

    public function passRecords(): \Generator
    {
        if ($this->transferor === null) {
            return;
        }

        yield from $this->passFrom($this->transferor);
    }
}
