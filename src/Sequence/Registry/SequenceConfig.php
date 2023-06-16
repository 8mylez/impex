<?php

namespace Dustin\ImpEx\Sequence\Registry;

class SequenceConfig implements PriorityInterface
{
    public function __construct(
        protected string $class,
        protected string $name,
        protected int $priority,
        protected ?string $parent = null
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getClass(): string
    {
        return $this->class;
    }
}
