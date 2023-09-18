<?php

namespace Dustin\ImpEx\PropertyAccess;

class AccessContext
{
    public const GET = 'get';

    public const SET = 'set';

    public const PUSH = 'push';

    public const MERGE = 'merge';

    public const COLLECT = 'collect';

    public const FLAG_NULL_ON_ERROR = 'null_on_error';

    public const FLAG_PUSH_ON_MERGE = 'push_on_merge';

    public const FLAG_COLLECT_NESTED = 'collect_nested';

    public const COLLECTOR_FIELD = '[]';

    public const WRITE_OPERATIONS = [
        self::PUSH,
        self::SET,
        self::MERGE,
    ];

    public const READ_OPERATIONS = [
        self::GET,
        self::COLLECT,
    ];

    private $flags = [];

    public function __construct(
        private string $operation,
        private string $rootOperation,
        private Path $path,
        string ...$flags
    ) {
        $this->flags = array_combine($flags, $flags);
    }

    public static function isWriteOperation(string $operation): bool
    {
        return \in_array($operation, self::WRITE_OPERATIONS);
    }

    public static function isReadOperation(string $operation): bool
    {
        return \in_array($operation, self::READ_OPERATIONS);
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
