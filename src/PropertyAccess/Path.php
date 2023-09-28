<?php

declare(strict_types=1);

namespace Dustin\ImpEx\PropertyAccess;

use Dustin\ImpEx\PropertyAccess\Exception\InvalidPathException;
use Dustin\ImpEx\Util\Value;

class Path implements \IteratorAggregate, \Countable
{
    /**
     * @var array
     */
    private $path = [];

    public function __construct(array|string|int|null $path = null)
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

    public function add(int|string $field): self
    {
        $this->validateField($field);
        $this->path[] = $field;

        return $this;
    }

    public function merge(self $path): self
    {
        $new = new Path($this->toArray());

        foreach ($path as $field) {
            $new->add($field);
        }

        return $new;
    }

    public function copy(): self
    {
        return new Path($this->toArray());
    }

    public function pop(): ?string
    {
        return array_pop($this->path);
    }

    public function shift(): ?string
    {
        return array_shift($this->path);
    }

    public function toArray(): array
    {
        return $this->path;
    }

    public function equals(self $path): bool
    {
        return empty(array_diff_assoc($this->toArray(), $path->toArray()));
    }

    public function __toString()
    {
        $path = [];

        foreach ($this->path as $field) {
            $path[] = str_replace('.', "\.", $field);
        }

        return implode('.', $path);
    }

    private function setPath(array|string|int $path): void
    {
        if (is_string($path)) {
            $path = $this->parse($path);
        } elseif (is_int($path)) {
            $path = [$path];
        }

        $position = 0;
        foreach ($path as $field) {
            $this->validateField($field, $position++);
            $this->path[] = $field;
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

        foreach ($result as &$field) {
            if (is_numeric($field)) {
                $field = intval($field);
            }
        }

        return $result;
    }

    private function validateField(int|string $field, ?int $position = null): void
    {
        if (Value::isEmpty($field)) {
            throw InvalidPathException::emptyField($position);
        }
    }
}
