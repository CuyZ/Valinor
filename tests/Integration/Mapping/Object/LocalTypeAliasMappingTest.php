<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class LocalTypeAliasMappingTest extends IntegrationTest
{
    public function test_values_are_mapped_properly(): void
    {
        $source = [
            'aliasWithEqualsSign' => 42,
            'aliasWithoutEqualsSign' => 42,
            'aliasShapedArray' => [
                'foo' => 'foo',
                'bar' => 1337,
            ],
            'aliasShapedArrayMultiline' => [
                'foo' => 'foo',
                'bar' => 1337,
            ],
            'aliasGeneric' => [42, 1337],
        ];

        foreach ([PhpStanLocalAliases::class, PsalmLocalAliases::class] as $class) {
            try {
                $result = (new MapperBuilder())
                    ->mapper()
                    ->map($class, $source);

                self::assertSame(42, $result->aliasWithEqualsSign);
                self::assertSame(42, $result->aliasWithoutEqualsSign);
                self::assertSame($source['aliasShapedArray'], $result->aliasShapedArray);
                self::assertSame($source['aliasShapedArrayMultiline'], $result->aliasShapedArrayMultiline);
                self::assertSame($source['aliasGeneric'], $result->aliasGeneric->aliasArray);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }
        }
    }

    public function test_type_aliases_are_imported_correctly(): void
    {
        foreach ([PhpStanAliasImport::class, PsalmAliasImport::class] as $class) {
            try {
                $result = (new MapperBuilder())
                    ->mapper()
                    ->map($class, [
                        'firstImportedType' => 42,
                        'secondImportedType' => 1337,
                    ]);

                self::assertSame(42, $result->firstImportedType);
                self::assertSame(1337, $result->secondImportedType);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }
        }
    }
}

/**
 * @template T
 * @phpstan-type AliasArray = T[]
 */
class GenericObjectWithPhpStanLocalAlias
{
    /** @var AliasArray */
    public array $aliasArray;
}

/**
 * Comment:
 * Some comment before types
 *
 * @phpstan-type AliasWithEqualsSign = int
 * @phpstan-type AliasWithoutEqualsSign int
 * @phpstan-type AliasShapedArray = array{foo: string, bar: int}
 * @phpstan-type AliasShapedArrayMultiline = array{
 *   foo: string,
 *   bar: int
 * }
 * @phpstan-type AliasGeneric = GenericObjectWithPhpStanLocalAlias<int>
 */
class PhpStanLocalAliases
{
    /** @var AliasWithEqualsSign */
    public int $aliasWithEqualsSign;

    /** @var AliasWithoutEqualsSign */
    public int $aliasWithoutEqualsSign;

    /** @var AliasShapedArray */
    public array $aliasShapedArray;

    /** @var AliasShapedArrayMultiline */
    public array $aliasShapedArrayMultiline;

    /** @var AliasGeneric */
    public GenericObjectWithPhpStanLocalAlias $aliasGeneric;
}

/**
 * @phpstan-type AliasWithoutEqualsSign int
 */
class AnotherPhpStanLocalAlias
{
    /** @var AliasWithoutEqualsSign */
    public int $aliasWithEqualsSign;
}

/**
 * Comment:
 * Some comment before import
 *
 * @phpstan-import-type AliasWithEqualsSign from PhpStanLocalAliases
 * @phpstan-import-type AliasWithoutEqualsSign from AnotherPhpStanLocalAlias
 */
class PhpStanAliasImport
{
    /** @var AliasWithEqualsSign */
    public int $firstImportedType;

    /** @var AliasWithoutEqualsSign */
    public int $secondImportedType;
}

/**
 * @template T
 * @psalm-type AliasArray = T[]
 */
class GenericObjectWithPsalmLocalAlias
{
    /** @var AliasArray */
    public array $aliasArray;
}

/**
 * Comment:
 * Some comment before types
 *
 * @psalm-type AliasWithEqualsSign = int
 * @psalm-type AliasWithoutEqualsSign int
 * @psalm-type AliasShapedArray = array{foo: string, bar: int}
 * @psalm-type AliasShapedArrayMultiline = array{
 *   foo: string,
 *   bar: int
 * }
 * @psalm-type AliasGeneric = GenericObjectWithPsalmLocalAlias<int>
 */
class PsalmLocalAliases
{
    /** @var AliasWithEqualsSign */
    public int $aliasWithEqualsSign;

    /** @var AliasWithoutEqualsSign */
    public int $aliasWithoutEqualsSign;

    /** @var AliasShapedArray */
    public array $aliasShapedArray;

    /** @var AliasShapedArrayMultiline */
    public array $aliasShapedArrayMultiline;

    /** @var AliasGeneric */
    public GenericObjectWithPsalmLocalAlias $aliasGeneric;
}

/**
 * @psalm-type AliasWithoutEqualsSign int
 */
class AnotherPsalmLocalAliases
{
    /** @var AliasWithoutEqualsSign */
    public int $aliasWithEqualsSign;
}

/**
 * Comment:
 * Some comment before import
 *
 * @psalm-import-type AliasWithEqualsSign from PsalmLocalAliases
 * @psalm-import-type AliasWithoutEqualsSign from AnotherPsalmLocalAliases
 */
class PsalmAliasImport
{
    /** @var AliasWithEqualsSign */
    public int $firstImportedType;

    /** @var AliasWithoutEqualsSign */
    public int $secondImportedType;
}
