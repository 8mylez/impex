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

        parent::__construct($attributePath, $data, static::buildMessage(...$errors));
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private static function buildMessage(AttributeConversionException ...$errors): string
    {
        $message = sprintf("Caught %s errors.\n", count($errors));

        foreach ($errors as $error) {
            $message .= sprintf(" • [%s] - %s\n", $error->getAttributePath(), $error->getMessage());
        }

        return $message;
    }
}
