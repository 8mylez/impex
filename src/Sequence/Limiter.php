<?php

namespace Dustin\ImpEx\Sequence;

class Limiter extends DirectPass
{
    public function __construct(protected int $limit)
    {
        if ($limit <= 0) {
            throw new \InvalidArgumentException('Limit must be greater than zero.');
        }
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
