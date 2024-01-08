<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\Container;

class Packer extends DirectPass
{
    public function __construct(protected ?int $batchSize = null)
    {
        if ($batchSize !== null && $batchSize <= 0) {
            throw new \InvalidArgumentException('Batch size must be greater than zero.');
        }
    }

    public function passFrom(Transferor $transferor): \Generator
    {
        $container = new Container();

        /** @var mixed $record */
        foreach ($transferor->passRecords() as $record) {
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
}
