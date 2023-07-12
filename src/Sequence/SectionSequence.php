<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\ImpEx\Encapsulation\TransferContainer;

class SectionSequence extends Sequence
{
    public function handle(Transferor $rootTransferor): void
    {
        $this->setTransferor($rootTransferor);
        $count = count($this->sections);
        $i = 0;

        /** @var Section $section */
        foreach ($this->sections as $section) {
            ++$i;
            if ($section instanceof Transferor) {
                $section->handle($this->transferor);
                $this->setTransferor($section);

                continue;
            }

            if (
                !($this->transferor instanceof TransferContainer) &&
                $i < $count
            ) {
                $container = $this->accommodateRecords($this->transferor);
                $this->setTransferor($container);
            }

            $section->handle($this->transferor);
        }
    }
}
