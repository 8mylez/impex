<?php

namespace Dustin\ImpEx\Sequence;

interface Transferor
{
    public function passRecords(): \Generator;
}
