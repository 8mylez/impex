<?php

namespace Dustin\ImpEx\DependencyInjection\Exception;

class EmptyTagAttributeException extends \Exception
{
    public function __construct(private string $tag, private string $attribute)
    {
        parent::__construct(sprintf(
            "Attribute '%s' for container tag '%s' cannot be empty!", $attribute, $tag
        ));
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }
}
