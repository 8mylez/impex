<?php

namespace Dustin\ImpEx\Serializer\Exception;

class AttributeConversionExceptionStack extends AttributeConversionException
{
    /**
     * @var array
     */
    private $errors = [];

    public function __construct(string $attributePath, array $data, AttributeConversionException ...$errors)
    {
        $this->errors = (array) $errors;

        parent::__construct($attributePath, $data, sprintf('Caught %s errors.', count($errors)));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
