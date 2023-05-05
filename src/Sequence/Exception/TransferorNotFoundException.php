<?php

namespace Dustin\ImpEx\Sequence\Exception;

class TransferorNotFoundException extends \Exception
{
    public function __construct(string $sequence)
    {
        parent::__construct(sprintf("A transferor for sequence '%s' was not found.", $sequence));
    }
}
