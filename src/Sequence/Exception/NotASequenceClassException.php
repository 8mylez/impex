<?php

namespace Dustin\ImpEx\Sequence\Exception;

use Dustin\ImpEx\Sequence\AbstractSequence;

class NotASequenceClassException extends \Exception
{
    public function __construct(string $class)
    {
        parent::__construct(sprintf('Sequence class must inherit from %s. %s given.', AbstractSequence::class, $class));
    }
}
