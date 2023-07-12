<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\ObjectMapping;
use Dustin\ImpEx\Sequence\Section;

class SectionRepository implements SectionRepositoryInterface
{
    /** @var ObjectMapping */
    private $sections;

    public function __construct()
    {
        $this->sections = ObjectMapping::create(Section::class);
    }

    public function getSection(string $id): ?Section
    {
        return $this->sections->get($id);
    }

    public function addSection(string $id, Section $section): void
    {
        $this->sections->set($id, $section);
    }

    public function hasSection(string $id): bool
    {
        return $this->sections->has($id);
    }
}
