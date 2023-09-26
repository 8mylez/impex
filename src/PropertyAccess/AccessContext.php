<?php

namespace Dustin\ImpEx\PropertyAccess;

class AccessContext
{
    public const NULL_ON_ERROR = 'null_on_error';

    public const PUSH_ON_MERGE = 'push_on_merge';

    public const COLLECT_NESTED = 'collect_nested';

    public const COLLECTOR_FIELD = '[]';

    private $flags = [];

    public function __construct(
        private string $operation,
        private string $rootOperation,
        private Path $path,
        string ...$flags
    ) {
        $this->flags = array_combine($flags, $flags);
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getRootOperation()
    {
        return $this->rootOperation;
    }

    public function getPath(): Path
    {
        return $this->path;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function hasFlag(string $flag): bool
    {
        return isset($this->flags[$flag]);
    }

    public function setFlag(string $flag): self
    {
        $this->flags[$flag] = $flag;

        return $this;
    }

    public function removeFlag(string $flag): self
    {
        unset($this->flags[$flag]);

        return $this;
    }

    public function createSubContext(string $operation, Path $path): self
    {
        return new AccessContext(
            $operation,
            $this->rootOperation,
            $path,
            ...$this->flags
        );
    }
}
