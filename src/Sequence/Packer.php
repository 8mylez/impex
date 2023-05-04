<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\Container;
use Dustin\Encapsulation\EncapsulationInterface;

class Packer extends Limiter
{
    private int $batchSize = -1;

    public function __construct(EncapsulationInterface $config)
    {
        $this->batchSize = $this->getBatchSize($config);

        parent::__construct($config);
    }

    public function passRecords(): \Generator
    {
        $container = new Container();

        /** @var mixed $record */
        foreach (parent::passRecords() as $record) {
            $container->add($record);

            if (count($container) === $this->batchSize) {
                yield $container;

                $container = new Container();
            }
        }

        if (count($container) > 0) {
            yield $container;
        }
    }

    private function getBatchSize(EncapsulationInterface $config): int
    {
        $batchSize = intval($config->get('batchSize'));

        return $batchSize > 0 ? $batchSize : -1;
    }
}
