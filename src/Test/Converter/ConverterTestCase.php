<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\ImpEx\PropertyAccess\Path;
use Dustin\ImpEx\Serializer\Converter\ConversionContext;
use PHPUnit\Framework\TestCase;

abstract class ConverterTestCase extends TestCase
{
    protected static function createDataFromFile(string $baseFileName): array
    {
        $jsonFile = \dirname((new \ReflectionClass(static::class))->getFileName()).'/'.$baseFileName;
        $data = static::readJson($jsonFile);

        static::applyModifiers($data);

        return $data;
    }

    protected static function readJson(string $file): array
    {
        return json_decode(file_get_contents($file), true);
    }

    protected static function applyModifiers(array &$data): void
    {
        foreach ($data as &$testData) {
            $testData['input'] = static::modify($testData['input']);
            $testData['expected'] = static::modify($testData['expected']);

            foreach ($testData['constructorParams'] as $key => $param) {
                $testData['constructorParams'][$key] = static::modify($param);
            }
        }
    }

    protected function createConversionContext(string $direction): ConversionContext
    {
        return new ConversionContext(
            new \stdClass(),
            new Path(['someAttribute']),
            'someAttribute',
            $direction,
            [],
            []
        );
    }

    protected static function modify(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        if (strpos($value, 'date|') === 0) {
            return \date_create_from_format('Y-m-d H:i:s', str_replace('date|', '', $value));
        }

        return $value;
    }
}
