<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ShapedArrayNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\ShapedArrayElementMissing;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;
use PHPUnit\Framework\TestCase;

final class ShapedArrayNodeBuilderTest extends TestCase
{
    public function test_invalid_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(FakeShell::any());
    }

    public function test_build_with_invalid_source_throws_exception(): void
    {
        $type = new ShapedArrayType(new ShapedArrayElement(new StringValueType('foo'), new FakeType('SomeType')));

        $this->expectException(SourceMustBeIterable::class);
        $this->expectExceptionCode(1618739163);
        $this->expectExceptionMessage("Value 'foo' does not match type `array{foo: SomeType}`.");

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(FakeShell::new($type, 'foo'));
    }

    public function test_build_with_invalid_source_for_shaped_array_containing_object_type_throws_exception(): void
    {
        $type = new ShapedArrayType(new ShapedArrayElement(new StringValueType('foo'), new FakeObjectType()));

        $this->expectException(SourceMustBeIterable::class);
        $this->expectExceptionCode(1618739163);
        $this->expectExceptionMessage("Invalid value 'foo'.");

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(FakeShell::new($type, 'foo'));
    }

    public function test_build_with_null_source_throws_exception(): void
    {
        $type = new ShapedArrayType(new ShapedArrayElement(new StringValueType('foo'), new FakeType('SomeType')));

        $this->expectException(SourceMustBeIterable::class);
        $this->expectExceptionCode(1618739163);
        $this->expectExceptionMessage("Cannot be empty and must be filled with a value matching type `array{foo: SomeType}`.");

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(FakeShell::new($type));
    }

    public function test_build_with_missing_key_throws_exception(): void
    {
        $this->expectException(ShapedArrayElementMissing::class);
        $this->expectExceptionCode(1631613641);
        $this->expectExceptionMessage("Missing element `foo` matching type `SomeType`.");

        $type = new ShapedArrayType(new ShapedArrayElement(new StringValueType('foo'), new FakeType('SomeType')));

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(FakeShell::new($type, []));
    }
}
