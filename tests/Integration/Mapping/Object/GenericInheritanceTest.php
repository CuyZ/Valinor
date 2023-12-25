<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Object;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Tests\Integration\IntegrationTest;

final class GenericInheritanceTest extends IntegrationTest
{
    public function test_generic_types_are_inherited_properly(): void
    {
        try {
            $object = (new MapperBuilder())
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
