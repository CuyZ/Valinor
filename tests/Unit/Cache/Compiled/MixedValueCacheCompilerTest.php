<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Cache\Compiled;

use CuyZ\Valinor\Cache\Compiled\MixedValueCacheCompiler;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MixedValueCacheCompilerTest extends TestCase
{
    private MixedValueCacheCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new MixedValueCacheCompiler();
    }

    /**
     * @dataProvider values_are_compiled_correctly_data_provider
     */
    public function test_values_are_compiled_correctly(mixed $value): void
    {
        $compiledValue = eval('return ' . $this->compiler->compile($value) . ';');

        self::assertEquals($value, $compiledValue);
    }

    public function values_are_compiled_correctly_data_provider(): iterable
    {
        yield 'Float' => [1337.42];
        yield 'Int' => [404];
        yield 'String' => ['Schwifty!'];
        yield 'True' => [true];
        yield 'False' => [false];
        yield 'Array of scalar' => [[1337.42, 404, 'Schwifty!', true, false]];
        yield 'Object' => [new stdClass()];
        yield 'Array with object' => [[new stdClass()]];
    }
}
