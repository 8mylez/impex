<?php

namespace Dustin\ImpEx\Sequence;

use Dustin\Encapsulation\EncapsulationInterface;

class Limiter implements RecordHandling, Transferor
{
    protected int $limit = -1;

    protected ?Transferor $transferor = null;

    public function __construct(EncapsulationInterface $config)
    {
        $this->limit = $this->getLimit($config);
    }

    public function handle(Transferor $transferor): void
    {
        $this->transferor = $transferor;
    }

    public function passRecords(): \Generator
    {
        if ($this->transferor === null) {
            return;
        }

        $fetched = 0;

        foreach ($this->transferor->passRecords() as $record) {
            ++$fetched;
            yield $record;

            if ($fetched === $this->limit) {
                break;
            }
        }
    }

    private function getLimit(EncapsulationInterface $config): int
    {
        $limit = intval($config->get('limit'));

        return $limit > 0 ? $limit : -1;
    }
}
