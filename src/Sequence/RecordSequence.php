<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\ImpEx\Encapsulation\TransferContainer;

class RecordSequence extends SectionSequence
{
    public function handle(Transferor $rootTransferor): void
    {
        /** @var mixed $record */
        foreach ($rootTransferor->passRecords() as $record) {
            parent::handle(new TransferContainer([$record]));
        }
    }
}
