<?php

namespace Dustin\ImpEx\Sequence\Registry\Config;

use Dustin\Encapsulation\PropertyEncapsulation;

class SectionDefinition extends PropertyEncapsulation
{
    public const TYPE_SECTION = 'section';

    public const TYPE_SEQUENCE = 'sequence';

    /**
     * @var string
     */
    protected $id;

    protected int $priority = 0;

    /**
     * @var string
     */
    protected $type;

    public function getId()
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }
}
