<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class FunctionsContainerTest extends TestCase
{
    public function test_keys_are_kept_when_iterating(): void
    {
        $functions = (new FunctionsContainer(new FakeFunctionDefinitionRepository(), [
            'foo' => fn () => 'foo',
            'bar' => fn () => 'bar',
        ]));

        $functions = iterator_to_array($functions);

        self::assertArrayHasKey('foo', $functions);
        self::assertArrayHasKey('bar', $functions);
    }

    public function test_function_object_remains_the_same(): void
    {
        $functions = (new FunctionsContainer(new FakeFunctionDefinitionRepository(), [fn () => 'foo']));

        $functionA = $functions->get(0);
        $functionB = $functions->get(0);

        self::assertSame($functionA, $functionB);
    }
}
