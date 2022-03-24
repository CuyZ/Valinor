<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition;

use CuyZ\Valinor\Definition\Exception\CallbackNotFound;
use CuyZ\Valinor\Definition\Exception\FunctionNotFound;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;
use CuyZ\Valinor\Tests\Fake\Definition\Repository\FakeFunctionDefinitionRepository;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

final class FunctionsContainerTest extends TestCase
{
    public function test_get_unknown_function_throws_exception(): void
    {
        $this->expectException(FunctionNotFound::class);
        $this->expectExceptionCode(1647523444);
        $this->expectExceptionMessage('The function `unknown` was not found.');

        (new FunctionsContainer(new FakeFunctionDefinitionRepository(), []))->get('unknown');
    }

    public function test_get_unknown_callback_throws_exception(): void
    {
        $function = FakeFunctionDefinition::new();

        $this->expectException(CallbackNotFound::class);
        $this->expectExceptionCode(1647523495);
        $this->expectExceptionMessage("The callback associated to `{$function->signature()}` could not be found.");

        (new FunctionsContainer(new FakeFunctionDefinitionRepository(), []))->callback($function);
    }

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
}
