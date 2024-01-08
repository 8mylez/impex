<?php

namespace Dustin\ImpEx\Test\Converter\Chunker;

use Dustin\ImpEx\Serializer\Converter\ArrayList\Chunker;
use Dustin\ImpEx\Serializer\Converter\ArrayList\GroupChunkStrategy;
use Dustin\ImpEx\Serializer\Converter\ArrayList\SizeChunkStrategy;
use Dustin\ImpEx\Serializer\Converter\BidirectionalConverter;
use Dustin\ImpEx\Test\Converter\BidirectionalConverterTestCase;

class ChunkerTest extends BidirectionalConverterTestCase
{
    protected function instantiateConverter(array $params = []): BidirectionalConverter
    {
        $strategy = null;

        switch ($params['strategy']) {
            case 'size':
                $strategy = new SizeChunkStrategy(
                    $params['chunkSize'],
                    $params['preserveKeys'] ?? false,
                    $params['strictChunkSize'] ?? false
                );
                break;
            case 'group':
                $strategy = new GroupChunkStrategy($params['groupKey']);
                break;
        }

        if ($strategy === null) {
            throw new \Exception('Strategy was not found.');
        }

        return new Chunker($strategy, ...($params['flags'] ?? []));
    }
}
