<?php

namespace Dustin\ImpEx\Encapsulation;

use Dustin\Encapsulation\Encapsulation;
use Dustin\Encapsulation\ImmutableEncapsulation;
use Dustin\Encapsulation\PropertyEncapsulation;

abstract class Record extends PropertyEncapsulation
{
    private ?Header $header = null;

    private ?ImmutableEncapsulation $raw = null;

    private ?Encapsulation $extension = null;

    private static $defaultProperties = [
        'header' => Header::class,
        'raw' => ImmutableEncapsulation::class,
        'extension' => Encapsulation::class,
    ];

    final public static function getDefaultProperties(): array
    {
        return static::$defaultProperties;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function set(string $field, $value): void
    {
        if (isset(static::$defaultProperties[$field])) {
            $this->{$field} = $value;

            return;
        }

        parent::set($field, $value);
    }

    public function getFields(): array
    {
        $fields = array_keys(static::$defaultProperties);
        $recordFields = parent::getFields();

        return array_unique(array_merge($fields, $recordFields));
    }

    public function hasExtension(): bool
    {
        return $this->extension != null;
    }

    public function getExtension(bool $autoCreate = false): ?Encapsulation
    {
        if (
            $this->extension == null &&
            $autoCreate === true
        ) {
            $this->extension = new Encapsulation();
        }

        return $this->extension;
    }

    public function hasHeader(): bool
    {
        return $this->header != null;
    }

    public function getHeader(bool $autoCreate = false): ?Header
    {
        if (
            $this->header == null &&
            $autoCreate === true
        ) {
            $this->header = new Header();
        }

        return $this->header;
    }

    public function hasRaw(): bool
    {
        return $this->raw != null;
    }

    public function getRaw(): ?ImmutableEncapsulation
    {
        return $this->raw;
    }
}
