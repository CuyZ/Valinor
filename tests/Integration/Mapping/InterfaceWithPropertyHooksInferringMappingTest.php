<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterface;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterfaceImplementation;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterfaceWithSeveralParents;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\ChildInterfaceWithSeveralParentsImplementation;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\GrandChildInterface;
use CuyZ\Valinor\Tests\Fixture\Object\InterfaceWithPropertyHooks\GrandChildInterfaceImplementation;
use CuyZ\Valinor\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\RequiresPhp;

// PHP8.4 move to InterfaceInferringMappingTest
#[RequiresPhp('>=8.4')]
final class InterfaceWithPropertyHooksInferringMappingTest extends IntegrationTestCase
{
    public function test_infer_interface_extending_interface_with_property_hooks_works_properly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(ChildInterface::class, fn () => ChildInterfaceImplementation::class)
                ->mapper()
                ->map(ChildInterface::class, Source::array([
                    'name' => 'foo',
                    'deleted' => null,
                ]));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(ChildInterfaceImplementation::class, $result);
        self::assertSame('foo', $result->name);
        self::assertNull($result->deleted);
    }

    public function test_infer_interface_extending_several_interfaces_with_property_hooks_works_properly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(ChildInterfaceWithSeveralParents::class, fn () => ChildInterfaceWithSeveralParentsImplementation::class)
                ->mapper()
                ->map(ChildInterfaceWithSeveralParents::class, Source::array([
                    'deleted' => 'foo',
                    'count' => 42,
                ]));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(ChildInterfaceWithSeveralParentsImplementation::class, $result);
        self::assertSame('foo', $result->deleted);
        self::assertSame(42, $result->count);
    }

    public function test_infer_interface_extending_chain_of_interfaces_with_property_hooks_works_properly(): void
    {
        try {
            $result = $this->mapperBuilder()
                ->infer(GrandChildInterface::class, fn () => GrandChildInterfaceImplementation::class)
                ->mapper()
                ->map(GrandChildInterface::class, Source::array([
                    'name' => 'foo',
                    'deleted' => 'bar',
                    'active' => true,
                ]));
        } catch (MappingError $error) {
            $this->mappingFail($error);
        }

        self::assertInstanceOf(GrandChildInterfaceImplementation::class, $result);
        self::assertSame('foo', $result->name);
        self::assertSame('bar', $result->deleted);
        self::assertTrue($result->active);
    }

    public function test_invalid_value_for_property_declared_in_parent_interface_throws_exception(): void
    {
        try {
            $this->mapperBuilder()
                ->infer(ChildInterface::class, fn () => ChildInterfaceImplementation::class)
                ->mapper()
                ->map(ChildInterface::class, Source::array([
                    'name' => 'foo',
                    'deleted' => 1337,
                ]));

            self::fail('No mapping error when one was expected');
        } catch (MappingError $exception) {
            self::assertMappingErrors($exception, [
                'deleted' => "[cannot_resolve_type_from_union] Value 1337 does not match any of `string`, `null`.",
            ]);
        }
    }
}
