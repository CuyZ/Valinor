<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Type\Types\Exception\InvalidUnionOfClassString;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\Exception\CannotCastValue;
use CuyZ\Valinor\Type\Types\Exception\InvalidClassString;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassStringTypeTest extends TestCase
{
    public function test_string_subtype_can_be_retrieved(): void
    {
        $subType = new FakeObjectType();

        self::assertSame($subType, (new ClassStringType($subType))->subType());
    }

    public function test_union_of_string_subtype_can_be_retrieved(): void
    {
        $subType = new UnionType(new FakeObjectType(), new FakeObjectType());

        self::assertSame($subType, (new ClassStringType($subType))->subType());
    }

    public function test_union_with_invalid_type_throws_exception(): void
    {
        $type = new UnionType(new FakeObjectType(), new FakeType());

        $this->expectException(InvalidUnionOfClassString::class);
        $this->expectExceptionCode(1648830951);
        $this->expectExceptionMessage("Type `$type` contains invalid class string element(s).");

        new ClassStringType($type);
    }

    public function test_accepts_correct_values(): void
    {
        $classStringType = new ClassStringType();

        self::assertTrue($classStringType->accepts(stdClass::class));
        self::assertTrue($classStringType->accepts(DateTimeInterface::class));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        $classStringType = new ClassStringType();

        self::assertFalse($classStringType->accepts(null));
        self::assertFalse($classStringType->accepts('Schwifty!'));
        self::assertFalse($classStringType->accepts(42.1337));
        self::assertFalse($classStringType->accepts(404));
        self::assertFalse($classStringType->accepts(['foo' => 'bar']));
        self::assertFalse($classStringType->accepts(false));
        self::assertFalse($classStringType->accepts(new stdClass()));
    }

    public function test_accepts_correct_values_with_sub_type(): void
    {
        $objectType = new FakeObjectType(DateTimeInterface::class);
        $classStringType = new ClassStringType($objectType);

        self::assertTrue($classStringType->accepts(DateTime::class));
        self::assertTrue($classStringType->accepts(DateTimeImmutable::class));
        self::assertTrue($classStringType->accepts(DateTimeInterface::class));

        self::assertFalse($classStringType->accepts(stdClass::class));
    }

    public function test_accepts_correct_values_with_union_sub_type(): void
    {
        $type = new UnionType(new FakeObjectType(DateTimeInterface::class), new FakeObjectType(stdClass::class));
        $classStringType = new ClassStringType($type);

        self::assertTrue($classStringType->accepts(DateTime::class));
        self::assertTrue($classStringType->accepts(DateTimeImmutable::class));
        self::assertTrue($classStringType->accepts(DateTimeInterface::class));

        self::assertTrue($classStringType->accepts(stdClass::class));
    }

    public function test_does_not_accept_incorrect_values_with_union_sub_type(): void
    {
        $unionType = new UnionType(new FakeObjectType(DateTime::class), new FakeObjectType(stdClass::class));
        $classStringType = new ClassStringType($unionType);

        self::assertFalse($classStringType->accepts(DateTimeImmutable::class));
    }

    public function test_can_cast_stringable_value(): void
    {
        self::assertTrue((new ClassStringType())->canCast('foo'));
    }

    public function test_cannot_cast_other_types(): void
    {
        $classStringType = new ClassStringType();

        self::assertFalse($classStringType->canCast(null));
        self::assertFalse($classStringType->canCast(42.1337));
        self::assertFalse($classStringType->canCast(404));
        self::assertFalse($classStringType->canCast(true));
        self::assertFalse($classStringType->canCast(['foo' => 'bar']));
        self::assertFalse($classStringType->canCast(new stdClass()));
    }

    public function test_cast_class_string_returns_class_string(): void
    {
        $result = (new ClassStringType())->cast(stdClass::class);

        self::assertSame(stdClass::class, $result);
    }

    public function test_cast_stringable_object_returns_class_string(): void
    {
        $result = (new ClassStringType())->cast(new StringableObject(stdClass::class));

        self::assertSame(stdClass::class, $result);
    }

    public function test_cast_to_sub_class_returns_sub_class(): void
    {
        $objectType = new FakeObjectType(DateTimeInterface::class);
        $result = (new ClassStringType($objectType))->cast(DateTime::class);

        self::assertSame(DateTime::class, $result);
    }

    public function test_cast_invalid_value_throws_exception(): void
    {
        $this->expectException(CannotCastValue::class);
        $this->expectExceptionCode(1603216198);
        $this->expectExceptionMessage('Cannot cast from `int` to `class-string`.');

        (new ClassStringType())->cast(42);
    }

    public function test_cast_invalid_class_string_throws_exception(): void
    {
        $classStringObject = new StringableObject('foo');

        $this->expectException(InvalidClassString::class);
        $this->expectExceptionCode(1608132562);
        $this->expectExceptionMessage("Invalid class string `foo`.");

        (new ClassStringType())->cast($classStringObject);
    }

    public function test_cast_invalid_class_string_of_object_type_throws_exception(): void
    {
        $objectType = new FakeObjectType();
        $classStringObject = new StringableObject(DateTimeInterface::class);

        $this->expectException(InvalidClassString::class);
        $this->expectExceptionCode(1608132562);
        $this->expectExceptionMessage("Invalid class string `DateTimeInterface`, it must be a subtype of `$objectType`.");

        (new ClassStringType($objectType))->cast($classStringObject);
    }

    public function test_cast_invalid_class_string_of_union_type_throws_exception(): void
    {
        $unionType = new UnionType(new FakeObjectType(DateTime::class), new FakeObjectType(stdClass::class));
        $classStringObject = new StringableObject(DateTimeInterface::class);

        $this->expectException(InvalidClassString::class);
        $this->expectExceptionCode(1608132562);
        $this->expectExceptionMessage("Invalid class string `DateTimeInterface`, it must be one of `DateTime`, `stdClass`.");

        (new ClassStringType($unionType))->cast($classStringObject);
    }

    public function test_string_value_is_correct(): void
    {
        $objectType = new FakeObjectType();

        self::assertSame('class-string', (string)new ClassStringType());
        self::assertSame("class-string<$objectType>", (string)new ClassStringType($objectType));
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new ClassStringType())->matches(new ClassStringType()));
    }

    public function test_matches_same_type_with_object_type(): void
    {
        $objectTypeA = new FakeObjectType();
        $objectTypeB = FakeObjectType::matching($objectTypeA);

        self::assertTrue((new ClassStringType($objectTypeB))->matches(new ClassStringType($objectTypeA)));
    }

    public function test_does_not_match_same_type_with_no_object_type(): void
    {
        self::assertFalse((new ClassStringType(new FakeObjectType()))->matches(new ClassStringType()));
    }

    public function test_does_not_match_same_type_with_invalid_object_type(): void
    {
        $classStringTypeA = new ClassStringType(new FakeObjectType(stdClass::class));
        $classStringTypeB = new ClassStringType(new FakeObjectType(DateTime::class));

        self::assertFalse($classStringTypeA->matches($classStringTypeB));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse((new ClassStringType())->matches(new FakeType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new ClassStringType())->matches(new MixedType()));
    }

    public function test_matches_string_type(): void
    {
        self::assertTrue((new ClassStringType())->matches(new NativeStringType()));
    }

    public function test_matches_non_empty_string_type(): void
    {
        self::assertTrue((new ClassStringType())->matches(new NonEmptyStringType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $objectTypeA = new FakeObjectType();
        $objectTypeB = FakeObjectType::matching($objectTypeA);

        $unionType = new UnionType(
            new FakeType(),
            new ClassStringType($objectTypeA),
            new FakeType(),
        );

        self::assertTrue((new ClassStringType($objectTypeB))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $classStringType = new ClassStringType(new FakeObjectType());
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($classStringType->matches($unionType));
    }
}
