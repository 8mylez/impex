<?php

namespace Dustin\ImpEx\Sequence;

interface RecordHandling
{
    public function handle(Transferor $transferor): void;
}
