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
            'aliasShapedArrayNested' => [
                'baz' => [
                    'foo' => 'foo',
                    'bar' => 1337,
                ]
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
                self::assertSame($source['aliasGeneric'], $result->aliasGeneric->aliasArray);
            } catch (MappingError $error) {
                $this->mappingFail($error);
            }
        }
    }

    public function test_type_aliases_are_imported_correctly(): void
    {
        foreach ([
            PhpStanAliasImport::class,
            PsalmAliasImport::class,
            PhpStanAliasImportFromInterface::class,
            PsalmAliasImportFromInterface::class
        ] as $class) {
            try {
                $result = (new MapperBuilder())
                    ->mapper()
                    ->map($class, 42);

                self::assertSame(42, $result->importedType);
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
 * @phpstan-type AliasWithEqualsSign = int
 * @phpstan-type AliasWithoutEqualsSign int
 * @phpstan-type AliasShapedArray = array{foo: string, bar: int}
 * @phpstan-type AliasGeneric = GenericObjectWithPhpStanLocalAlias<int>
 * @phpstan-type AliasShapedArrayNested = array{baz: AliasShapedArray}
 */
class PhpStanLocalAliases
{
    /** @var AliasWithEqualsSign */
    public int $aliasWithEqualsSign;

    /** @var AliasWithoutEqualsSign */
    public int $aliasWithoutEqualsSign;

    /** @var AliasShapedArray */
    public array $aliasShapedArray;

    /** @var AliasShapedArrayNested */
    public array $aliasShapedArrayNested;

    /** @var AliasGeneric */
    public GenericObjectWithPhpStanLocalAlias $aliasGeneric;
}

/**
 * @phpstan-type AliasWithEqualsSign = int
 * @phpstan-type AliasWithoutEqualsSign int
 * @phpstan-type AliasShapedArray = array{foo: string, bar: int}
 * @phpstan-type AliasShapedArrayNested = array{baz: AliasShapedArray}
 * @phpstan-type AliasGeneric = GenericObjectWithPhpStanLocalAlias<int>
 */
interface PhpStanLocalAliasesInterface
{
}

/**
 * @phpstan-import-type AliasWithEqualsSign from PhpStanLocalAliases
 */
class PhpStanAliasImport
{
    /** @var AliasWithEqualsSign */
    public int $importedType;
}


/**
 * @phpstan-import-type AliasWithEqualsSign from PhpStanLocalAliasesInterface
 */
class PhpStanAliasImportFromInterface
{
    /** @var AliasWithEqualsSign */
    public int $importedType;
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
 * @psalm-type AliasWithEqualsSign = int
 * @psalm-type AliasWithoutEqualsSign int
 * @psalm-type AliasShapedArray = array{foo: string, bar: int}
 * @psalm-type AliasShapedArrayNested = array{baz: AliasShapedArray}
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

    /** @var AliasShapedArrayNested */
    public array $aliasShapedArrayNested;

    /** @var AliasGeneric */
    public GenericObjectWithPsalmLocalAlias $aliasGeneric;
}

/**
 * @psalm-type AliasWithEqualsSign = int
 * @psalm-type AliasWithoutEqualsSign int
 * @psalm-type AliasShapedArray = array{foo: string, bar: int}
 * @psalm-type AliasShapedArrayNested = array{baz: AliasShapedArray}
 * @psalm-type AliasGeneric = GenericObjectWithPsalmLocalAlias<int>
 */
interface PsalmLocalAliasesInterface
{
}

/**
 * @psalm-import-type AliasWithEqualsSign from PsalmLocalAliases
 */
class PsalmAliasImport
{
    /** @var AliasWithEqualsSign */
    public int $importedType;
}

/**
 * @psalm-import-type AliasWithEqualsSign from PsalmLocalAliasesInterface
 */
class PsalmAliasImportFromInterface
{
    /** @var AliasWithEqualsSign */
    public int $importedType;
}