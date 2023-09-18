<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidOperationException;

class AccessOperation
{
    /**
     * @var array
     */
    private $flags = [];

    public function __construct(
        private string $path,
        private string $operation,
        string ...$flags
    ) {
        $this->flags = $flags;
    }

    public function execute(mixed &$data, mixed $value = null): mixed
    {
        switch ($this->operation) {
            case AccessContext::GET:
                return PropertyAccessor::get($this->getPath(), $data, ...$this->getFlags());
            case AccessContext::SET:
                return PropertyAccessor::set($this->getPath(), $data, $value, ...$this->getFlags());
            case AccessContext::PUSH:
                return PropertyAccessor::push($this->getPath(), $data, $value, ...$this->getFlags());
            case AccessContext::MERGE:
                return PropertyAccessor::merge($this->getPath(), $data, $value, ...$this->getFlags());
            case AccessContext::COLLECT:
                return PropertyAccessor::collect($this->getPath(), $data, ...$this->getFlags());
        }

        throw new InvalidOperationException($this->operation);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getOperation(): string
    {
        return $this->operation;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }
}
