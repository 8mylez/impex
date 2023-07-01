<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\Encapsulation\Container;

class PriorityContainer extends Container
{
    public function sortByPriority(): self
    {
        $this->sort(function (PriorityInterface $a, PriorityInterface $b) {
            return $a->getPriority() <=> $b->getPriority();
        });

        return $this;
    }

    protected function getAllowedClass(): ?string
    {
        return PriorityInterface::class;
    }
}
