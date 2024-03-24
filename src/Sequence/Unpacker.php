<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\Container;

class Unpacker extends DirectPass
{
    public function passFrom(Transferor $transferor): \Generator
    {
        foreach ($transferor->passRecords() as $record) {
            if ($record instanceof Container) {
                yield from $record;

                continue;
            }

            yield $record;
        }
    }
}
