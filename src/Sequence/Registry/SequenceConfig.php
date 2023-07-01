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

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }

    public function setParent(?string $parent): void
    {
        $this->parent = $parent;
    }
}
