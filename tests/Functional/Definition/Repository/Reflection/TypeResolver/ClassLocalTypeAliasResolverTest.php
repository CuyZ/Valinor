<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassLocalTypeAliasResolver;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NativeClassType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ClassLocalTypeAliasResolverTest extends TestCase
{
    private ClassLocalTypeAliasResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ClassLocalTypeAliasResolver(
            new LexingTypeParserFactory(),
        );
    }

    /**
     * @param class-string $className
     * @param array<non-empty-string, non-empty-string> $expectedAliases
     */
    #[DataProvider('local_type_alias_is_resolved_properly_data_provider')]
    public function test_local_type_alias_is_resolved_properly(string $className, array $expectedAliases): void
    {
        $aliases = $this->resolver->resolveLocalTypeAliases(new NativeClassType($className));
        $aliases = array_map(
            fn (Type $type) => $type->toString(),
            $aliases,
        );

        sort($expectedAliases);
        sort($aliases);

        self::assertSame($expectedAliases, $aliases);
    }

    public static function local_type_alias_is_resolved_properly_data_provider(): iterable
    {
        yield 'PHPStan alias' => [
            'className' => (
                /**
                 * @phpstan-type PhpStanNonEmptyStringAlias=non-empty-string
                 */
                new class () {}
            )::class,
            [
                'PhpStanNonEmptyStringAlias' => 'non-empty-string',
            ]
        ];

        yield 'PHPStan alias with spaces between equal sign' => [
            'className' => (
                /**
                 * @phpstan-type PhpStanNonEmptyStringAlias = non-empty-string
                 */
                new class () {}
            )::class,
            [
                'PhpStanNonEmptyStringAlias' => 'non-empty-string',
            ]
        ];

        yield 'Psalm alias' => [
            'className' => (
                /**
                 * @phpstan-type PsalmNonEmptyStringAlias=non-empty-string
                 */
                new class () {}
            )::class,
            [
                'PsalmNonEmptyStringAlias' => 'non-empty-string',
            ]
        ];

        yield 'Psalm alias with spaces between equal sign' => [
            'className' => (
                /**
                 * @phpstan-type PsalmNonEmptyStringAlias = non-empty-string
                 */
                new class () {}
            )::class,
            [
                'PsalmNonEmptyStringAlias' => 'non-empty-string',
            ]
        ];

        yield 'last type has precedence' => [
            'className' => (
                /**
                 * @phpstan-type SomeType = non-empty-string
                 * @phpstan-type SomeType = int<42, 1337>
                 */
                new class () {}
            )::class,
            [
                'SomeType' => 'int<42, 1337>',
            ]
        ];

        yield 'types can be nested' => [
            'className' => (
                /**
                 * @phpstan-type SomeType = non-empty-string
                 * @phpstan-type SomeNestedType = array<SomeType>
                 */
                new class () {}
            )::class,
            [
                'SomeType' => 'non-empty-string',
                'SomeNestedType' => 'array<non-empty-string>',
            ]
        ];

        yield 'PHPStan type can use a Psalm type' => [
            'className' => (
                /**
                 * @psalm-type SomeType = non-empty-string
                 * @phpstan-type SomeNestedType = array<SomeType>
                 */
                new class () {}
            )::class,
            [
                'SomeType' => 'non-empty-string',
                'SomeNestedType' => 'array<non-empty-string>',
            ]
        ];

        yield 'Psalm type can use a PHPStan type' => [
            'className' => (
                /**
                 * @phpstan-type SomeType = non-empty-string
                 * @psalm-type SomeNestedType = array<SomeType>
                 */
                new class () {}
            )::class,
            [
                'SomeType' => 'non-empty-string',
                'SomeNestedType' => 'array<non-empty-string>',
            ]
        ];
    }
}
