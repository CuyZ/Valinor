<?php

declare(strict_types=1);

use CuyZ\Valinor\Mapper\ArgumentsMapper;

/**
 * @param mixed $input
 * @return array{foo: string, bar?: int|null}
 */
function mappingFunctionArgumentsWillInferObjectOfSameType(ArgumentsMapper $mapper, $input): array
{
    return $mapper->mapArguments(fn (string $foo, int $bar = null): string => "$foo / $bar", $input);
}

/**
 * @param mixed $input
 * @return array{foo: string, bar?: int|null}
 */
function mappingStaticMethodArgumentsWillInferObjectOfSameType(ArgumentsMapper $mapper, $input): array
{
    // PHP8.1 First-class callable syntax
    return $mapper->mapArguments(Closure::fromCallable([SomeClass::class, 'someStaticMethod']), $input);
}

/**
 * @param mixed $input
 * @return array{foo: string, bar?: int|null}
 */
function mappingMethodArgumentsWillInferObjectOfSameType(ArgumentsMapper $mapper, $input): array
{
    // PHP8.1 First-class callable syntax
    return $mapper->mapArguments(Closure::fromCallable([new SomeClass(), 'someMethod']), $input);
}

/** @internal */
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
