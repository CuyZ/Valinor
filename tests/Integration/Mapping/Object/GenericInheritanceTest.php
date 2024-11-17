<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;

final class GenericInheritanceTest extends IntegrationTestCase
{
    public function test_generic_types_are_inherited_properly(): void
    {
        try {
            $object = $this->mapperBuilder()
                ->mapper()
                ->map(ChildClassWithInheritedGenericType::class, [
                    'valueA' => 'foo',
                    'valueB' => 1337,
                    'valueC' => 'bar',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->valueA);
        self::assertSame(1337, $object->valueB);
        self::assertSame('bar', $object->valueC);
    }

    public function test_phpstan_annotated_generic_types_are_inherited_properly(): void
    {
        try {
            $object = $this->mapperBuilder()
                ->mapper()
                ->map(ChildClassWithPhpStanAnnotations::class, [
                    'valueA' => 'foo',
                    'valueB' => 1337,
                    'valueC' => 'bar',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->valueA);
        self::assertSame(1337, $object->valueB);
        self::assertSame('bar', $object->valueC);
    }

    public function test_psalm_annotated_generic_types_are_inherited_properly(): void
    {
        try {
            $object = $this->mapperBuilder()
                ->mapper()
                ->map(ChildClassWithPsalmAnnotations::class, [
                    'valueA' => 'foo',
                    'valueB' => 1337,
                    'valueC' => 'bar',
                ]);
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertSame('foo', $object->valueA);
        self::assertSame(1337, $object->valueB);
        self::assertSame('bar', $object->valueC);
    }
}

/**
 * @template FirstTemplate Some comment
 * @template SecondTemplate Some comment
 */
abstract class ParentClassWithGenericTypes
{
    /** @var FirstTemplate */
    public $valueA;

    /** @var SecondTemplate */
    public $valueB;
}

/**
 * @template FirstTemplate
 * @extends ParentClassWithGenericTypes<FirstTemplate, int> Some comment
 */
abstract class SecondParentClassWithGenericTypes extends ParentClassWithGenericTypes
{
    /** @var FirstTemplate */
    public $valueC;
}

/**
 * @extends SecondParentClassWithGenericTypes<string> Some comment
 */
final class ChildClassWithInheritedGenericType extends SecondParentClassWithGenericTypes {}

/**
 * @phpstan-template FirstTemplate Some comment
 * @phpstan-template SecondTemplate Some comment
 */
abstract class ParentClassWithPhpStanGenericTypes
{
    /** @var FirstTemplate */
    public $valueA;

    /** @var SecondTemplate */
    public $valueB;
}

/**
 * @phpstan-template FirstTemplate
 * @phpstan-extends ParentClassWithPhpStanGenericTypes<FirstTemplate, int> Some comment
 */
abstract class SecondParentClassWithPhpStanAnnotations extends ParentClassWithPhpStanGenericTypes
{
    /** @var FirstTemplate */
    public $valueC;
}

/**
 * @phpstan-extends SecondParentClassWithPhpStanAnnotations<string> Some comment
 */
final class ChildClassWithPhpStanAnnotations extends SecondParentClassWithPhpStanAnnotations {}

/**
 * @psalm-template FirstTemplate Some comment
 * @psalm-template SecondTemplate Some comment
 */
abstract class ParentClassWithPsalmGenericTypes
{
    /** @var FirstTemplate */
    public $valueA;

    /** @var SecondTemplate */
    public $valueB;
}

/**
 * @psalm-template FirstTemplate
 * @psalm-extends ParentClassWithPsalmGenericTypes<FirstTemplate, int> Some comment
 *
 * @phpstan-ignore missingType.generics (It seems PHPStan doesn't support the `@psalm-extends` tag)
 */
abstract class SecondParentClassWithPsalmAnnotations extends ParentClassWithPsalmGenericTypes
{
    /** @var FirstTemplate */
    public $valueC;
}

/**
 * @psalm-extends SecondParentClassWithPsalmAnnotations<string> Some comment
 *
 * @phpstan-ignore missingType.generics (It seems PHPStan doesn't support the `@psalm-extends` tag)
 */
final class ChildClassWithPsalmAnnotations extends SecondParentClassWithPsalmAnnotations {}
