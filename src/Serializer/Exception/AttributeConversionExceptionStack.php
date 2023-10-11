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
        $this->errors = $errors;

        parent::__construct($attributePath, $data, static::createMessage(...$errors), []);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorCount(): int
    {
        $count = 0;

        foreach ($this->errors as $error) {
            $count += $error->getErrorCount();
        }

        return $count;
    }

    private function createMessage(AttributeConversionException ...$errors): string
    {
        $message = sprintf("Caught %s errors.\n", $this->getErrorCount());

        foreach ($errors as $error) {
            $message .= sprintf(" • [%s] - %s\n", $error->getAttributePath(), $error->getMessage());
        }

        return trim($message);
    }
}
