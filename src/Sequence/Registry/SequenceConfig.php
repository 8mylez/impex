<?php

namespace Dustin\ImpEx\Sequence\Registry;

class SequenceConfig implements PriorityInterface
{
    protected string $class;

    protected string $name;

    protected ?string $parent;

    protected int $priority;

    public function __construct(
        string $class, string $name, int $priority, string $parent = null
    ) {
        $this->class = $class;
        $this->name = $name;
        $this->parent = $parent;
        $this->priority = $priority;
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
