<?php

namespace Dustin\ImpEx\PropertyAccess;

class AccessContext
{
    public const GET = 'get';

    public const SET = 'set';

    public const PUSH = 'push';

    public const FLAG_NULL_ON_ERROR = 'null_on_error';

    public const WRITE_OPERATIONS = [
        self::PUSH,
        self::SET,
    ];

    public const READ_OPERATIONS = [
        self::GET,
    ];

    private $flags = [];

    public function __construct(
        private string $operation,
        private string $rootOperation,
        private string $path,
        string ...$flags
    ) {
        $this->flags = $flags;
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

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function hasFlag(string $flag): bool
    {
        return in_array($flag, $this->flags);
    }

    public function createSubContext(string $operation, string $path): self
    {
        return new AccessContext(
            $operation,
            $this->rootOperation,
            $path,
            ...$this->flags
        );
    }
}
