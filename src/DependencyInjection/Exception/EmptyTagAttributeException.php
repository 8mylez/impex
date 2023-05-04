<?php

namespace Dustin\ImpEx\DependencyInjection\Exception;

class EmptyTagAttributeException extends \Exception
{
    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $attribute;

    public function __construct(string $tag, string $attribute)
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
