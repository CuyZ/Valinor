<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\StaticAnalysis;

use Closure;
use CuyZ\Valinor\Mapper\ArgumentsMapper;
use CuyZ\Valinor\Mapper\TreeMapper;

use function PHPStan\Testing\assertType;

function mapping_scalar_will_infer_correct_type(TreeMapper $mapper): void
{
    $bool = $mapper->map('bool', true);
    $float = $mapper->map('float', 42.1337);
    $int = $mapper->map('int', 42);
    $string = $mapper->map('string', 'foo');

    /** @psalm-check-type $bool = bool */
    assertType('bool', $bool);
    /** @psalm-check-type $float = float */
    assertType('float', $float);
    /** @psalm-check-type $int = int */
    assertType('int', $int);
    /** @psalm-check-type $string = string */
    assertType('string', $string);
}

function mapping_shaped_array_will_infer_correct_type(TreeMapper $mapper): void
{
    $result = $mapper->map('array{foo: string, bar?: int}', []);

    /** @psalm-check-type $result = array{foo: string, bar?: int} */
    assertType('array{foo: string, bar?: int}', $result);
}

function mapping_union_of_types_will_infer_correct_type(TreeMapper $mapper): void
{
    $result = $mapper->map('float|int', 42);

    /** @psalm-check-type $result = float|int */
    assertType('float|int', $result);
}

function mapping_function_arguments_will_infer_object_of_same_type(ArgumentsMapper $mapper): void
{
    $result = $mapper->mapArguments(fn (string $foo, int $bar = null): string => "$foo / $bar", []);

    /** @psalm-check-type $result = array{foo: string, bar?: int|null} */
    assertType('array{foo: string, bar?: int|null}', $result);
}

function mapping_static_method_arguments_will_infer_object_of_same_type(ArgumentsMapper $mapper): void
{
    $result = $mapper->mapArguments(Closure::fromCallable(SomeClass::someStaticMethod(...)), []);

    /** @psalm-check-type $result = array{foo: string, bar?: int|null} */
    assertType('array{foo: string, bar?: int|null}', $result);
}

function mapping_method_arguments_will_infer_object_of_same_type(ArgumentsMapper $mapper): void
{
    $result = $mapper->mapArguments(Closure::fromCallable((new SomeClass())->someMethod(...)), []);

    /** @psalm-check-type $result = array{foo: string, bar?: int|null} */
    assertType('array{foo: string, bar?: int|null}', $result);
}

final class SomeClass
{
    public static function someStaticMethod(string $foo, int $bar = null): string
    {
        return "$foo / $bar";
    }

    public function someMethod(string $foo, int $bar = null): string
    {
        return "$foo / $bar";
    }
}
