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

    public function add(\Throwable ...$exceptions): void
    {
        foreach ($exceptions as $key => $e) {
            if (!$e instanceof AttributeConversionExceptionInterface) {
                throw new \InvalidArgumentException(sprintf('Argument #%s must be %s. %s given.', $key, AttributeConversionExceptionInterface::class, get_debug_type($e)));
            }
        }

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
