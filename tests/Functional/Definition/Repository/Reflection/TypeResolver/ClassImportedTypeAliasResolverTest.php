<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Reflection\TypeResolver;

use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClass;
use CuyZ\Valinor\Definition\Exception\InvalidTypeAliasImportClassType;
use CuyZ\Valinor\Definition\Exception\UnknownTypeAliasImport;
use CuyZ\Valinor\Definition\Repository\Reflection\TypeResolver\ClassImportedTypeAliasResolver;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NativeClassType;
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
            new LexingTypeParserFactory(),
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
                'ArrayOfGenericAlias' => 'non-empty-array<string>',
            ],
        ];

        yield 'class importing PHPStan alias with comments' => [
            'className' => SomeClassImportingAliasWithComments::class,
            'expectedImportedAliases' => [
                'NonEmptyStringAlias' => 'non-empty-string',
                'IntegerRangeAlias' => 'int<42, 1337>',
                'MultilineShapedArrayAlias' => 'array{foo: string, bar: int}',
                'ArrayOfGenericAlias' => 'non-empty-array<string>',
            ],
        ];

        yield 'class importing PHPStan alias with empty imports' => [
            'className' => SomeClassImportingAliasWithEmptyImports::class,
            'expectedImportedAliases' => [
                'NonEmptyStringAlias' => 'non-empty-string',
            ],
        ];
    }

    public function test_class_with_invalid_type_alias_import_class_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from UnknownType
             */
            (new class () {})::class;

        $this->expectException(InvalidTypeAliasImportClass::class);
        $this->expectExceptionCode(1638535486);
        $this->expectExceptionMessage("Cannot import a type alias from unknown class `UnknownType` in class `$class`.");

        $this->resolver->resolveImportedTypeAliases(new NativeClassType($class));
    }

    public function test_class_with_invalid_type_alias_import_class_type_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from string
             */
            (new class () {})::class;

        $this->expectException(InvalidTypeAliasImportClassType::class);
        $this->expectExceptionCode(1638535608);
        $this->expectExceptionMessage("Importing a type alias can only be done with classes, `string` was given in class `$class`.");

        $this->resolver->resolveImportedTypeAliases(new NativeClassType($class));
    }

    public function test_class_with_unknown_type_alias_import_throws_exception(): void
    {
        $class =
            /**
             * @phpstan-import-type T from stdClass
             */
            (new class () {})::class;

        $this->expectException(UnknownTypeAliasImport::class);
        $this->expectExceptionCode(1638535757);
        $this->expectExceptionMessage("Type alias `T` imported in `$class` could not be found in `stdClass`");

        $this->resolver->resolveImportedTypeAliases(new NativeClassType($class));
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
 * @template T
 * @phpstan-type ArrayOfGenericAlias = non-empty-array<T>
 */
final class SomeClassWithGenericLocalAlias {}

/**
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithLocalAlias
 * @phpstan-import-type IntegerRangeAlias from AnotherClassWithLocalAlias
 * @phpstan-import-type MultilineShapedArrayAlias from SomeClassWithLocalAlias
 * @phpstan-import-type ArrayOfGenericAlias from SomeClassWithGenericLocalAlias<string>
 *
 * @phpstan-ignore-next-line / PHPStan cannot infer an import type from class with generic
 */
final class SomeClassImportingAlias {}

/**
 * Some comment
 *
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithLocalAlias Here is some comment
 * @phpstan-import-type IntegerRangeAlias from AnotherClassWithLocalAlias Another comment
 * @phpstan-import-type MultilineShapedArrayAlias from SomeClassWithLocalAlias Yet another comment
 * @phpstan-import-type ArrayOfGenericAlias from SomeClassWithGenericLocalAlias<string> And another comment
 *
 * Another comment
 *
 * @phpstan-ignore-next-line / PHPStan cannot infer an import type from class with generic
 */
final class SomeClassImportingAliasWithComments {}

/**
 * Empty imported type:
 * @phpstan-import-type
 *
 * Imported type with missing class:
 * @phpstan-import-type SomeType from
 *
 * @phpstan-import-type NonEmptyStringAlias from SomeClassWithLocalAlias
 *
 * Empty imported type:
 * @phpstan-import-type
 *
 * Imported type with missing class:
 * @phpstan-import-type SomeType from
 *
 * @phpstan-ignore-next-line / Invalid annotations are here on purpose to test them
 */
final class SomeClassImportingAliasWithEmptyImports {}
