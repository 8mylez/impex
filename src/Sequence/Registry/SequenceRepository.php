<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\ObjectMapping;
use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;

class SequenceRepository implements SequenceRepositoryInterface
{
    /**
     * @var ObjectMapping
     */
    private $sequences;

    public function __construct()
    {
        $this->sequences = ObjectMapping::create(SequenceDefinition::class);
    }

    public function addSequence(SequenceDefinition $definition): void
    {
        $this->sequences->set($definition->getId(), $definition);
    }

    public function getSequence(string $id): ?SequenceDefinition
    {
        return $this->sequences->get($id);
    }

    public function hasSequence(string $id): bool
    {
        return $this->sequences->has($id);
    }
}
