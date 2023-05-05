<?php

namespace Dustin\ImpEx\Sequence\Exception;

class MultipleTransferorsException extends \Exception
{
    public function __construct(string $sequence)
    {
        parent::__construct(sprintf("Multiple transferors were found for sequence '%s'.", $sequence));
    }
}
