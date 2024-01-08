<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\ImpEx\Encapsulation\TransferContainer;

abstract class Sequence implements Section
{
    protected ?Transferor $transferor = null;

    protected SectionContainer $sections;

    final public function __construct(Section ...$sections)
    {
        $this->sections = new SectionContainer($sections);
    }

    protected function setTransferor(?Transferor $transferor): void
    {
        $this->transferor = $transferor;
    }

    protected function accommodateRecords(Transferor $transferor, ?TransferContainer $container = null): TransferContainer
    {
        if ($container === null) {
            $container = new TransferContainer();
        }

        $container->accommodate($transferor);

        return $container;
    }
}
