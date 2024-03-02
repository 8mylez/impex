<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\Container;
use Dustin\Encapsulation\EncapsulationInterface;

class Packer extends DirectPass
{
    public function __construct(protected ?int $batchSize = null)
    {
        if ($batchSize !== null && $batchSize <= 0) {
            throw new \InvalidArgumentException('Batch size must be greater than zero.');
        }
    }

    public static function createFrom(EncapsulationInterface $encapsulation, string $field = 'batchSize'): self
    {
        $batchSize = $encapsulation->get($field);

        if (!is_int($batchSize) && !is_null($batchSize)) {
            throw new \UnexpectedValueException('Expected batch size to be int or null. %s given.', get_debug_type($batchSize));
        }

        return new self($batchSize);
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
