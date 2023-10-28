<?php

namespace Dustin\ImpEx\Serializer\Exception;

class AttributeConversionExceptionStack extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_CONVERSION__ERRORS';

    /**
     * @var array
     */
    private $errors = [];

    public function __construct(string $attributePath, array $data, AttributeConversionException ...$errors)
    {
        $this->errors = $errors;

        parent::__construct($attributePath, $data, static::createMessage(), [], self::ERROR_CODE);
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

    private function createMessage(): string
    {
        $messages = [sprintf('Caught %s errors.', $this->getErrorCount())];

        array_push($messages, ...$this->getMessages());

        return implode("\n", $messages);
    }
}
