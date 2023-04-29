<?php

namespace Dustin\ImpEx\Test\Converter;

use Dustin\Encapsulation\Encapsulation;
use PHPUnit\Framework\TestCase;

abstract class UnidirectionalConverterTestCase extends TestCase
{
    abstract protected function strict(): bool;

    abstract public function conversionProvider(): array;

    /**
     * @dataProvider conversionProvider
     */
    public function testConversion($input, $expectedResult, ?string $exception = null, array $constructorParams = [])
    {
        if ($exception !== null) {
            $this->expectException($exception);
        }

        $converter = $this->instantiateConverter(...$constructorParams);

        $result = $converter->convert($input, new Encapsulation(), '', '', []);

        if ($this->strict()) {
            $this->assertSame($result, $expectedResult);
        } else {
            $this->assertEquals($result, $expectedResult);
        }
    }
}
