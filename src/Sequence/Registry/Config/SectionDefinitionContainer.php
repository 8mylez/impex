<?php

namespace Dustin\ImpEx\Sequence\Registry\Config;

use Dustin\Encapsulation\Container;

class SectionDefinitionContainer extends Container
{
    public function sortByPriority(): self
    {
        $this->sort(function (SectionDefinition $a, SectionDefinition $b) {
            $prioA = $a->getPriority();
            $prioB = $b->getPriority();

            if ($prioA === $prioB) {
                return 0;
            }

            return $prioA > $prioB ? -1 : 1;
        });

        return $this;
    }

    public function filterByType(string $type): self
    {
        return $this->filter(function (SectionDefinition $section) use ($type) {
            return $section->getType() === $type;
        });
    }

    protected function getAllowedClass(): ?string
    {
        return SectionDefinition::class;
    }
}
