<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ShapedArrayNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\ShapedArrayElementMissing;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Shell;
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

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(Shell::root(new FakeType(), []));
    }

    public function test_build_with_invalid_source_throws_exception(): void
    {
        $type = new ShapedArrayType(new ShapedArrayElement(new StringValueType('foo'), new FakeType('SomeType')));

        $this->expectException(SourceMustBeIterable::class);
        $this->expectExceptionCode(1618739163);
        $this->expectExceptionMessage("Source must be iterable in order to be cast to `$type`, but is of type `string`.");

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(Shell::root($type, 'foo'));
    }

    public function test_build_with_null_source_throws_exception(): void
    {
        $type = new ShapedArrayType(new ShapedArrayElement(new StringValueType('foo'), new FakeType('SomeType')));

        $this->expectException(SourceMustBeIterable::class);
        $this->expectExceptionCode(1618739163);
        $this->expectExceptionMessage("Cannot cast an empty value to `$type`.");

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(Shell::root($type, null));
    }

    public function test_build_with_missing_key_throws_exception(): void
    {
        $this->expectException(ShapedArrayElementMissing::class);
        $this->expectExceptionCode(1631613641);
        $this->expectExceptionMessage("Missing value `foo` of type `SomeType`.");

        $type = new ShapedArrayType(new ShapedArrayElement(new StringValueType('foo'), new FakeType('SomeType')));

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(Shell::root($type, []));
    }
}
