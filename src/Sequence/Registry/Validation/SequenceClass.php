<?php

namespace Dustin\ImpEx\Sequence\Registry\Validation;

use Symfony\Component\Validator\Constraint;

class SequenceClass extends Constraint
{
    public string $message = '{{ string }} is not a valid sequence class.';
}
