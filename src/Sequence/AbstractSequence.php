<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\ImpEx\Encapsulation\TransferContainer;

abstract class AbstractSequence implements RecordHandling
{
    protected ?Transferor $transferor = null;

    protected array $handlers = [];

    final public function __construct(RecordHandling ...$handlers)
    {
        $this->handlers = $handlers;
    }

    protected function setTransferor(?Transferor $transferor)
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
