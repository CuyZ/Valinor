<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Reflection\TypeResolver;

use Closure;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\FunctionReturnTypeResolver;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ReflectionTypeResolver;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithConstants;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionFunction;

final class FunctionReturnTypeResolverTest extends UnitTestCase
{
    #[DataProvider('function_return_type_is_resolved_properly_data_provider')]
    public function test_function_return_type_is_resolved_properly(callable $callable, string $expectedType): void
    {
        $reflection = new ReflectionFunction(Closure::fromCallable($callable));
        $type = $this->functionReturnTypeResolver()->resolveReturnTypeFor($reflection);

        self::assertNotInstanceOf(UnresolvableType::class, $type);
        self::assertSame($expectedType, $type->toString());
    }

    public static function function_return_type_is_resolved_properly_data_provider(): iterable
    {
        yield 'phpdoc' => [
            /** @return int */
            fn () => 42,
            'int',
        ];

        yield 'phpdoc followed by new line' => [
            /**
             * @return int
             *
             */
            fn () => 42,
            'int',
        ];

        yield 'phpdoc literal string' => [
            /** @return 'foo' */
            fn () => 'foo',
            "'foo'",
        ];

        yield 'phpdoc union with space between types' => [
            /** @return int | float Some comment */
            fn (string $foo): int|float => $foo === 'foo' ? 42 : 1337.42,
            'int|float',
        ];

        yield 'phpdoc shaped array on several lines' => [
            /**
             * @return array{
             *     foo: string,
             *     bar: int,
             * } Some comment
             */
            fn () => ['foo' => 'foo', 'bar' => 42],
            'array{foo: string, bar: int}',
        ];

        yield 'phpdoc const with joker' => [
            /** @return ObjectWithConstants::CONST_WITH_STRING_VALUE_* */
            fn (): string => ObjectWithConstants::CONST_WITH_STRING_VALUE_A,
            "'some string value'|'another string value'",
        ];

        yield 'phpdoc enum with joker' => [
            /** @return BackedStringEnum::BA* */
            fn () => BackedStringEnum::BAR,
            BackedStringEnum::class . '::BA*',
        ];

        yield 'psalm' => [
            /** @psalm-return int */
            fn () => 42,
            'int',
        ];

        yield 'psalm trailing' => [
            /**
             * @return int
             * @psalm-return positive-int
             */
            fn () => 42,
            'positive-int',
        ];

        yield 'psalm leading' => [
            /**
             * @psalm-return positive-int
             * @return int
             */
            fn () => 42,
            'positive-int',
        ];

        yield 'phpstan' => [
            /** @phpstan-return int */
            fn () => 42,
            'int',
        ];

        yield 'phpstan trailing' => [
            /**
             * @return int
             * @phpstan-return positive-int
             */
            fn () => 42,
            'positive-int',
        ];

        yield 'phpstan leading' => [
            /**
             * @phpstan-return positive-int
             * @return int
             */
            fn () => 42,
            'positive-int',
        ];

        yield 'phpstan trailing after psalm' => [
            /**
             * @psalm-return int
             * @phpstan-return positive-int
             */
            fn () => 42,
            'positive-int',
        ];
    }

    private function functionReturnTypeResolver(): FunctionReturnTypeResolver
    {
        return new FunctionReturnTypeResolver(
            new ReflectionTypeResolver(
                (new TypeParserFactory())->buildDefaultTypeParser(),
                (new TypeParserFactory())->buildAdvancedTypeParserForClass(self::class),
            ),
        );
    }
}
