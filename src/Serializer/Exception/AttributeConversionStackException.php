<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\ErrorCodeException;
use Dustin\Exception\StackException;

class AttributeConversionStackException extends StackException implements AttributeConversionExceptionInterface
{
    public const ERROR_CODE = 'IMPEX_CONVERSION__ERRORS';

    public function __construct(private string $attributePath, private array $data, AttributeConversionExceptionInterface ...$errors)
    {
        parent::__construct(null, ...$errors);
        ErrorCodeException::__construct($this->createMessage(...$errors), []);
    }

    public function getErrorCount(): int
    {
        return $this->countErrors(...$this->getErrors());
    }

    public function getMessages(): array
    {
        return $this->getMessagesFromErrors(...$this->getErrors());
    }

    public function getAttributePath(): string
    {
        return $this->attributePath;
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function createMessage(AttributeConversionExceptionInterface ...$errors): string
    {
        $messages = [sprintf('Caught %s errors.', $this->countErrors(...$errors))];

        array_push($messages, ...$this->getMessagesFromErrors(...$errors));

        return implode("\n", $messages);
    }

    private function countErrors(AttributeConversionExceptionInterface ...$errors): int
    {
        $count = 0;

        foreach ($errors as $error) {
            $count += $error->getErrorCount();
        }

        return $count;
    }

    private function getMessagesFromErrors(AttributeConversionExceptionInterface ...$errors): array
    {
        $messages = [];

        foreach ($errors as $error) {
            foreach ($error->getMessages() as $message) {
                if (!$error instanceof self) {
                    $message = sprintf(' • [%s] - %s', $error->getAttributePath(), trim($message));
                }

                $messages[] = $message;
            }
        }

        return $messages;
    }
}
