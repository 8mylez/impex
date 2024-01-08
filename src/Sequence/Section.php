<?php

namespace Dustin\ImpEx\Sequence;

interface Section
{
    public function handle(Transferor $transferor): void;
}
