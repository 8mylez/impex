<?php

namespace Dustin\ImpEx\Serializer\Exception;

class InvalidContainerElementException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_INVALID_CONTAINER_ELEMENT';

    public function __construct(
        string $attributepath,
        array $data,
        string $containerClass,
        string $type
    ) {
        parent::__construct(
            $attributepath,
            $data,
            'Container of class {{ containerClass }} cannot hold element of type {{ type }}.',
            ['containerClass' => $containerClass, 'type' => $type]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
