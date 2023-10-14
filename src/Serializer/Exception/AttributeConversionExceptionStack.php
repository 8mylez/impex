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

    public function getMessages(): array
    {
        $messages = [];

        foreach ($this->errors as $error) {
            foreach ($error->getMessages() as $message) {
                if (!$error instanceof self) {
                    $message = sprintf(' • [%s] - %s', $error->getAttributePath(), trim($message));
                }

                $messages[] = $message;
            }
        }

        return $messages;
    }

    private function createMessage(AttributeConversionException ...$errors): string
    {
        $messages = [sprintf('Caught %s errors.', $this->getErrorCount())];

        array_push($messages, ...$this->getMessages());

        return implode("\n", $messages);
    }
}
