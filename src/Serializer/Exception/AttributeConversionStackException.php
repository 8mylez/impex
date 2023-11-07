<?php

namespace Dustin\ImpEx\Serializer\Exception;

use Dustin\Exception\StackException;

class AttributeConversionStackException extends StackException implements AttributeConversionExceptionInterface
{
    public const ERROR_CODE = 'IMPEX_CONVERSION__ERRORS';

    public function __construct(private string $attributePath, private array $data, AttributeConversionExceptionInterface ...$errors)
    {
        parent::__construct($this->createMessage(), ...$errors);
    }

    public function getErrorCount(): int
    {
        $count = 0;

        foreach ($this->getErrors() as $error) {
            $count += $error->getErrorCount();
        }

        return $count;
    }

    public function getMessages(): array
    {
        $messages = [];

        foreach ($this->getErrors() as $error) {
            foreach ($error->getMessages() as $message) {
                if (!$error instanceof self) {
                    $message = sprintf(' • [%s] - %s', $error->getAttributePath(), trim($message));
                }

                $messages[] = $message;
            }
        }

        return $messages;
    }

    public function getAttributePath(): string
    {
        return $this->attributePath;
    }

    public function getData(): array
    {
        return $this->data;
    }

    private function createMessage(): string
    {
        $messages = [sprintf('Caught %s errors.', $this->getErrorCount())];

        array_push($messages, ...$this->getMessages());

        return implode("\n", $messages);
    }
}
