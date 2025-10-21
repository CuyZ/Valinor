<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassLocalTypeAliasResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_map;
use function sort;

final class ClassLocalTypeAliasResolverTest extends TestCase
{
    private ClassLocalTypeAliasResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ClassLocalTypeAliasResolver(
            new TypeParserFactory(),
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
    }

    public function test_can_resolve_local_types_for_enum(): void
    {
        $aliases = $this->resolver->resolveLocalTypeAliases(EnumType::native(SomeEnumWithLocalTypeAlias::class));

        self::assertInstanceOf(NonEmptyStringType::class, $aliases['SomeEnumAlias']);
    }
}

/**
 * @phpstan-type SomeEnumAlias = non-empty-string
 */
enum SomeEnumWithLocalTypeAlias
{
    case Foo;
}
