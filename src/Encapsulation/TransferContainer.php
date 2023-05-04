<?php

namespace Dustin\ImpEx\Encapsulation;

use Dustin\Encapsulation\Container;
use Dustin\ImpEx\Sequence\Transferor;

class TransferContainer extends Container implements Transferor
{
    public function passRecords(): \Generator
    {
        yield from $this->toArray();
    }

    public function accommodate(Transferor $transferor): void
    {
        foreach ($transferor->passRecords() as $record) {
            $this->add($record);
        }
    }
}
