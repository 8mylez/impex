<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\EncapsulationInterface;

class Limiter extends DirectPass
{
    protected int $limit = -1;

    public function __construct(EncapsulationInterface $config)
    {
        $this->limit = $this->getLimit($config);
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

    protected function getLimit(EncapsulationInterface $config): int
    {
        $limit = intval($config->get('limit'));

        return $limit > 0 ? $limit : -1;
    }
}
