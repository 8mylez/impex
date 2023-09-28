<?php

declare(strict_types=1);

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\NotAccessableException;
use Dustin\ImpEx\Util\Type;

class AccessContext
{
    public const STRICT = 'strict';

    public const COLLECT_NESTED = 'collect_nested';

    public const COLLECTOR_FIELD = '[]';

    /**
     * @var array
     */
    private $flags = [];

    /**
     * @var array
     */
    private $accesses = [];

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

    public function getRootOperation(): string
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

    public function addAccess(string $operation, Access $access): void
    {
        $this->accesses[$operation] = $access;
    }

    public function access(string|array|Path $path, mixed &$data, mixed $value = null): mixed
    {
        $access = $this->accesses[$this->operation] ?? null;

        if ($access === null) {
            throw new NotAccessableException($path, Type::getDebugType($data), $this->operation);
        }

        return $access->access($path, $data, $value, $this);
    }

    public function subContext(string $operation, Path $appendPath): self
    {
        $context = new AccessContext(
            $operation,
            $this->rootOperation,
            $this->path->merge($appendPath),
            ...$this->flags
        );

        foreach ($this->accesses as $key => $value) {
            $context->addAccess($key, $value);
        }

        return $context;
    }
}
