<?php

namespace Dustin\ImpEx\Sequence\Registry\Config;

use Dustin\Encapsulation\PropertyEncapsulation;

class SequenceDefinition extends PropertyEncapsulation
{
    public function __construct(array $data = [])
    {
        if (!isset($data['sections'])) {
            $data['sections'] = new SectionDefinitionContainer();
        }

        parent::__construct($data);
    }

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $class = null;

    protected SectionDefinitionContainer $sections;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    public function getSections(): SectionDefinitionContainer
    {
        return $this->sections;
    }

    public function setSections(SectionDefinitionContainer $sections): void
    {
        $this->sections = $sections;
    }
}
