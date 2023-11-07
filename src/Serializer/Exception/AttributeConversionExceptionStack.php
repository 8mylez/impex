<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\ExceptionStack;
use Dustin\Exception\StackException;

class AttributeConversionExceptionStack extends ExceptionStack
{
    public function __construct(
        private string $attributePath,
        private array $data,
        AttributeConversionExceptionInterface ...$errors)
    {
        parent::__construct(...$errors);
    }

    public function add(AttributeConversionExceptionInterface ...$exceptions): void
    {
        parent::add(...$exceptions);
    }

    public function throw(): void
    {
        try {
            parent::throw();
        } catch (StackException $e) {
            throw new AttributeConversionStackException($this->attributePath, $this->data, ...$e->getErrors());
        }
    }
}
