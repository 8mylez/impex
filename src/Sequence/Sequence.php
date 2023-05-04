<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\ImpEx\Encapsulation\TransferContainer;

class Sequence extends AbstractSequence
{
    public function handle(Transferor $rootTransferor): void
    {
        $this->transferor = $rootTransferor;
        $count = count($this->handlers);
        $i = 0;

        /** @var RecordHandling $recordHandler */
        foreach ($this->handlers as $recordHandler) {
            ++$i;
            if ($recordHandler instanceof Transferor) {
                $recordHandler->handle($this->transferor);
                $this->setTransferor($recordHandler);

                continue;
            }

            if (
                !($this->transferor instanceof TransferContainer) &&
                $i < $count
            ) {
                $container = $this->accommodateRecords($this->transferor);
                $this->setTransferor($container);
            }

            $recordHandler->handle($this->transferor);
        }
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
