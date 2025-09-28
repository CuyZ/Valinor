<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassImportedTypeAliasResolver;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

use function array_map;
use function sort;

final class ClassImportedTypeAliasResolverTest extends TestCase
{
    private ClassImportedTypeAliasResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new ClassImportedTypeAliasResolver(
            new TypeParserFactory(),
        );
    }

    /**
     * @param class-string $className
     * @param array<non-empty-string, non-empty-string> $expectedImportedAliases
     */
    #[DataProvider('type_alias_are_imported_properly_data_provider')]
    public function test_type_alias_are_imported_properly(string $className, array $expectedImportedAliases): void
    {
        $importedAliases = $this->resolver->resolveImportedTypeAliases(new NativeClassType($className));
        $importedAliases = array_map(
            fn (Type $type) => $type->toString(),
            $importedAliases,
        );

        sort($expectedImportedAliases);
        sort($importedAliases);

        self::assertSame($expectedImportedAliases, $importedAliases);
    }

    public static function type_alias_are_imported_properly_data_provider(): iterable
    {
        yield 'class importing PHPStan and Psalm alias' => [
            'className' => SomeClassImportingPhpStanAndPsalmAlias::class,
            'expectedImportedAliases' => [
                'NonEmptyStringAlias' => 'non-empty-string',
                'IntegerRangeAlias' => 'int<42, 1337>',
            ],
        ];

        yield 'class importing PHPStan alias' => [
            'className' => SomeClassImportingAlias::class,
            'expectedImportedAliases' => [
                'NonEmptyStringAlias' => 'non-empty-string',
                'IntegerRangeAlias' => 'int<42, 1337>',
                'MultilineShapedArrayAlias' => 'array{foo: string, bar: int}',
            ],
        ];

        yield 'class importing PHPStan alias with comments' => [
            'className' => SomeClassImportingAliasWithComments::class,
            'expectedImportedAliases' => [
                'NonEmptyStringAlias' => 'non-empty-string',
                'IntegerRangeAlias' => 'int<42, 1337>',
                'MultilineShapedArrayAlias' => 'array{foo: string, bar: int}',
            ],
        ];

        yield 'class importing PHPStan alias with empty imports' => [
            'className' => SomeClassImportingAliasWithEmptyImports::class,
            'expectedImportedAliases' => [
                'NonEmptyStringAlias' => 'non-empty-string',
            ],
        ];
    }

    public function test_class_with_invalid_alias_import_type_throws_exception(): void
    {
        $class = SomeClassWithInvalidAliasImport::class;
        $aliases = $this->resolver->resolveImportedTypeAliases(new NativeClassType($class));

        self::assertInstanceOf(UnresolvableType::class, $aliases['T']);
        self::assertSame("Invalid type alias import `T` in class `$class`, a valid class name is expected but `Invalid-Class-Name` was given.", $aliases['T']->message());

        self::assertInstanceOf(NonEmptyStringType::class, $aliases['NonEmptyStringAlias']);
    }

    public function test_class_with_invalid_alias_import_class_type_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from string
             */
            (new class () {})::class;

        $aliases = $this->resolver->resolveImportedTypeAliases(new NativeClassType($class));

        self::assertInstanceOf(UnresolvableType::class, $aliases['T']);
        self::assertSame("Invalid type alias import `T` in class `$class`, a valid class name is expected but `string` was given.", $aliases['T']->message());
    }

    public function test_class_with_unknown_type_alias_import_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from stdClass
             */
            (new class () {})::class;

        $aliases = $this->resolver->resolveImportedTypeAliases(new NativeClassType($class));

        self::assertInstanceOf(UnresolvableType::class, $aliases['T']);
        self::assertSame("Type alias `T` imported in `$class` could not be found in `stdClass`", $aliases['T']->message());
    }
}

/**
 * @phpstan-type NonEmptyStringAlias = non-empty-string
 * @psalm-type IntegerRangeAlias = int<42, 1337>
 */
final class SomeClassWithPhpStanAndPsalmLocalAlias {}

/**
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithPhpStanAndPsalmLocalAlias
 * @psalm-import-type IntegerRangeAlias from SomeClassWithPhpStanAndPsalmLocalAlias
 */
final class SomeClassImportingPhpStanAndPsalmAlias {}

/**
 * @phpstan-type NonEmptyStringAlias = non-empty-string
 * @phpstan-type MultilineShapedArrayAlias = array{
 *   foo: string,
 *   bar: int,
 * }
 */
final class SomeClassWithLocalAlias {}

/**
 * @phpstan-type IntegerRangeAlias = int<42, 1337>
 */
final class AnotherClassWithLocalAlias {}

/**
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithLocalAlias
 * @phpstan-import-type IntegerRangeAlias from AnotherClassWithLocalAlias
 * @phpstan-import-type MultilineShapedArrayAlias from SomeClassWithLocalAlias
 */
final class SomeClassImportingAlias {}

/**
 * Some comment
 *
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithLocalAlias Here is some comment (@phpstan-ignore-line)
 * @phpstan-import-type IntegerRangeAlias from AnotherClassWithLocalAlias Another comment
 * @phpstan-import-type MultilineShapedArrayAlias from SomeClassWithLocalAlias Yet another comment
 *
 * Another comment
 */
final class SomeClassImportingAliasWithComments {}

/**
 * Empty imported type (@phpstan-ignore-next-line)
 * @phpstan-import-type
 *
 * Imported type with missing class (@phpstan-ignore-next-line)
 * @phpstan-import-type SomeType from
 *
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithLocalAlias
 */
final class SomeClassImportingAliasWithEmptyImports {}

/**
 * @phpstan-import-type T from Invalid-Class-Name
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithLocalAlias
 * @phpstan-ignore class.notFound
 */
final class SomeClassWithInvalidAliasImport {}
