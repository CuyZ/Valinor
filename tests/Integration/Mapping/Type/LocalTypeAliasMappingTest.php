<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Type;

use CuyZ\Valinor\Mapper\MappingError;
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
            'aliasGeneric' => [42, 1337],
        ];

        foreach ([PhpStanLocalAliases::class, PsalmLocalAliases::class] as $class) {
            try {
                $result = $this->mapperBuilder
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
 */
class PhpStanLocalAliases
{
    /** @var AliasWithEqualsSign */
    public int $aliasWithEqualsSign;

    /** @var AliasWithoutEqualsSign */
    public int $aliasWithoutEqualsSign;

    /** @var AliasShapedArray */
    public array $aliasShapedArray;

    /** @var AliasGeneric */
    public GenericObjectWithPhpStanLocalAlias $aliasGeneric;
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

    /** @var AliasGeneric */
    public GenericObjectWithPsalmLocalAlias $aliasGeneric;
}
