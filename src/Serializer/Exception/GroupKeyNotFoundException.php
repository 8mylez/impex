<?php

namespace Dustin\ImpEx\Serializer\Exception;

class GroupKeyNotFoundException extends AttributeConversionException
{
    public const ERROR_CODE = 'IMPEX_GROUP_KEY_NOT_FOUND';

    public function __construct(
        string $attributePath,
        array $data,
        string $path,
        string $type
    ) {
        parent::__construct(
            $attributePath,
            $data,
            'Group key ({{ path }}) could not be fetched from value of type {{ type }}.',
            ['path' => $path, 'type' => $type]
        );
    }

    public function getErrorCode(): string
    {
        return self::ERROR_CODE;
    }
}
