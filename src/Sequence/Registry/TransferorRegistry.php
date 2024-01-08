<?php

namespace Dustin\ImpEx\Sequence\Registry;

use Dustin\ImpEx\Sequence\Exception\MultipleTransferorsException;
use Dustin\ImpEx\Sequence\Exception\TransferorNotFoundException;
use Dustin\ImpEx\Sequence\Transferor;

class TransferorRegistry
{
    private $transferors = [];

    public function addTransferor(Transferor $transferor, string $sequence): void
    {
        if (isset($this->transferors[$sequence])) {
            throw new MultipleTransferorsException($sequence);
        }

        $this->transferors[$sequence] = $transferor;
    }

    public function getTransferor(string $sequence): Transferor
    {
        if (!isset($this->transferors[$sequence])) {
            throw new TransferorNotFoundException($sequence);
        }

        return $this->transferors[$sequence];
    }

    public function hasTransferor(string $sequence): bool
    {
        return isset($this->transferors[$sequence]);
    }
}
