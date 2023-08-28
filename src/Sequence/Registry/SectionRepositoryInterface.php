<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\Section;

interface SectionRepositoryInterface
{
    public function addSection(string $id, Section $section): void;

    public function getSection(string $id): ?Section;

    public function hasSection(string $id): bool;
}
