<?php

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidPathException;
use Dustin\ImpEx\Util\Value;

class Path implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $path = [];

    public function __construct(array|string|null $path = null)
    {
        if ($path !== null) {
            $this->setPath($path);
        }
    }

    public function getIterator(): \Traversable
    {
        yield from $this->path;
    }

    public function count(): int
    {
        return count($this->path);
    }

    public function isEmpty(): bool
    {
        return empty($this->path);
    }

    public function add(string $field): self
    {
        $this->validateField($field);
        $this->path[] = $field;

        return $this;
    }

    public function toArray(): array
    {
        return $this->path;
    }

    public function __toString()
    {
        $path = [];

        foreach ($this->path as $field) {
            $path[] = str_replace('.', "\.", $field);
        }

        return implode('.', $path);
    }

    private function setPath(array|string $path): void
    {
        if (is_string($path)) {
            $path = $this->parse($path);
        }

        $position = 0;
        foreach ($path as $field) {
            $this->add($field, $position++);
        }
    }

    private function parse(string $path): array
    {
        $result = [];
        $escaped = false;
        $currentPart = '';
        $position = -1;

        foreach (str_split($path) as $character) {
            ++$position;
            if ($character === '\\') {
                $escaped = !$escaped;

                if (!$escaped) {
                    $currentPart .= $character;
                }

                continue;
            }

            if ($character === '.' && !$escaped) {
                if (Value::isEmpty($currentPart)) {
                    throw InvalidPathException::unexpectedCharacter($path, $character, $position);
                }

                $result[] = $currentPart;
                $currentPart = '';

                continue;
            }

            if ($escaped && $character !== '.') {
                $currentPart .= '\\';
            }

            $currentPart .= $character;
            $escaped = false;
        }

        if ($escaped) {
            $currentPart .= '\\';
        }

        if (!Value::isEmpty($currentPart)) {
            $result[] = $currentPart;
        } elseif (!empty($result)) {
            throw InvalidPathException::unexpectedCharacter($path, '.', $position);
        }

        return $result;
    }

    private function validateField(string $field, ?int $position = null): void
    {
        if (!is_string($field) || Value::isEmpty($field)) {
            throw InvalidPathException::emptyField($position);
        }
    }
}
