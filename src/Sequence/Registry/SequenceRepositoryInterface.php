<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;

interface SequenceRepositoryInterface
{
    public function addSequence(SequenceDefinition $definition): void;

    public function getSequence(string $name): ?SequenceDefinition;

    public function hasSequence(string $name): bool;
}
