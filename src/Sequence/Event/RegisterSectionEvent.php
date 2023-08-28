<?php

namespace Dustin\ImpEx\Sequence\Event;

use Dustin\ImpEx\Sequence\Registry\Config\SequenceDefinition;
use Dustin\ImpEx\Sequence\Section;
use Symfony\Contracts\EventDispatcher\Event;

class RegisterSectionEvent extends Event
{
    public function __construct(
        private string $sectionId,
        private int $priority,
        private Section $section,
        private SequenceDefinition $sequenceDefinition
    ) {
    }

    public function getSectionId(): string
    {
        return $this->sectionId;
    }

    public function setSectionId(string $sectionId): void
    {
        $this->sectionId = $sectionId;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function setSection(Section $section): void
    {
        $this->section = $section;
    }

    public function getSequenceDefinition(): SequenceDefinition
    {
        return $this->sequenceDefinition;
    }
}
