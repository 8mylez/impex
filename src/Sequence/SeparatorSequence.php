<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\ImpEx\Encapsulation\TransferContainer;

class SeparatorSequence extends Sequence
{
    public function handle(Transferor $rootTransferor): void
    {
        /** @var mixed $record */
        foreach ($rootTransferor->passRecords() as $record) {
            parent::handle(new TransferContainer([$record]));
        }
    }
}
