<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\EncapsulationInterface;

class Limiter extends DirectPass
{
    public function __construct(protected ?int $limit = null)
    {
        if ($limit !== null && $limit <= 0) {
            throw new \InvalidArgumentException('Limit must be greater than zero.');
        }
    }

    public static function createFrom(EncapsulationInterface $encapsulation, string $field = 'limit'): self
    {
        $limit = $encapsulation->get($field);

        if (!is_int($limit) && !is_null($limit)) {
            throw new \UnexpectedValueException(sprintf('Expected limit to be int or null. %s given.', get_debug_type($limit)));
        }

        return new self($limit);
    }

    public function passFrom(Transferor $transferor): \Generator
    {
        $fetched = 0;

        foreach ($transferor->passRecords() as $record) {
            ++$fetched;
            yield $record;

            if ($fetched === $this->limit) {
                break;
            }
        }
    }
}
