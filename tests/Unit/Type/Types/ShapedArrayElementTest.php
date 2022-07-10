<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeFixedType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidShapeElementType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\StringValueType;
use PHPUnit\Framework\TestCase;

final class ShapedArrayElementTest extends TestCase
{
    public function test_element_properties_can_be_retrieved(): void
    {
        $key = new StringValueType('foo');
        $type = new FakeType();
        $optional = true;

        $element = new ShapedArrayElement($key, $type, $optional);

        self::assertSame($key, $element->key());
        self::assertSame($type, $element->type());
        self::assertSame($optional, $element->isOptional());
    }

    public function test_string_value_is_correct(): void
    {
        $key = new StringValueType('foo');
        $type = new FakeType();

        $element = new ShapedArrayElement($key, $type);
        $optionalElement = new ShapedArrayElement($key, $type, true);

        self::assertSame("foo: {$type->toString()}", $element->toString());
        self::assertSame("foo?: {$type->toString()}", $optionalElement->toString());
    }

    public function test_fixed_type_throws_exception(): void
    {
        $this->expectException(InvalidShapeElementType::class);
        $this->expectExceptionCode(1631294135);
        $this->expectExceptionMessage('The shaped array element `foo` cannot contain a fixed type `bar`.');

        new ShapedArrayElement(new StringValueType('foo'), new FakeFixedType('bar'));
    }
}
