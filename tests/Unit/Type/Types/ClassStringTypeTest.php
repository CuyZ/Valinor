<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\Exception\InvalidUnionOfClassString;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NonEmptyStringType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassStringTypeTest extends TestCase
{
    use TestIsSingleton;

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
        $this->expectExceptionMessage("Type `{$type->toString()}` contains invalid class string element(s).");

        ClassStringType::from($type);
    }

    #[TestWith([stdClass::class])]
    #[TestWith([DateTimeInterface::class])]
    public function test_basic_class_string_accepts_correct_values(mixed $value): void
    {
        $type = new ClassStringType();

        self::assertTrue($type->accepts($value));
        self::assertTrue($this->compiledAccept($type, $value));
    }

    #[TestWith(['accepts' => true, 'value' => DateTime::class])]
    #[TestWith(['accepts' => true, 'value' => DateTimeImmutable::class])]
    #[TestWith(['accepts' => true, 'value' => DateTimeInterface::class])]
    #[TestWith(['accepts' => false, 'value' => stdClass::class])]
    public function test_object_class_string_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = new ClassStringType(new FakeObjectType(DateTimeInterface::class));

        self::assertSame($accepts, $type->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($type, $value));
    }

    #[TestWith(['accepts' => true, 'value' => DateTime::class])]
    #[TestWith(['accepts' => true, 'value' => stdClass::class])]
    #[TestWith(['accepts' => false, 'value' => DateTimeImmutable::class])]
    public function test_union_of_objects_class_string_accepts_correct_values(bool $accepts, mixed $value): void
    {
        $type = new ClassStringType(new UnionType(new FakeObjectType(DateTime::class), new FakeObjectType(stdClass::class)));

        self::assertSame($accepts, $type->accepts($value));
        self::assertSame($accepts, $this->compiledAccept($type, $value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        $basicClassStringType = new ClassStringType();
        $objectClassStringType = new ClassStringType(new FakeObjectType());
        $unionClassStringType = new ClassStringType(new UnionType(new FakeObjectType(), new FakeObjectType()));

        self::assertFalse($basicClassStringType->accepts($value));
        self::assertFalse($objectClassStringType->accepts($value));
        self::assertFalse($unionClassStringType->accepts($value));

        self::assertFalse($this->compiledAccept($basicClassStringType, $value));
        self::assertFalse($this->compiledAccept($objectClassStringType, $value));
        self::assertFalse($this->compiledAccept($unionClassStringType, $value));
    }

    public function test_can_cast_stringable_value(): void
    {
        self::assertTrue((new ClassStringType())->canCast(stdClass::class));
    }

    public function test_cannot_cast_other_types(): void
    {
        $classStringType = new ClassStringType();

        self::assertFalse($classStringType->canCast(null));
        self::assertFalse($classStringType->canCast(42.1337));
        self::assertFalse($classStringType->canCast(404));
        self::assertFalse((new ClassStringType())->canCast('foo'));
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
        $this->expectException(AssertionError::class);

        (new ClassStringType())->cast(42);
    }

    public function test_cast_invalid_class_string_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $classStringObject = new StringableObject('foo');

        (new ClassStringType())->cast($classStringObject);
    }

    public function test_cast_invalid_class_string_of_object_type_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $objectType = new FakeObjectType();
        $classStringObject = new StringableObject(DateTimeInterface::class);

        (new ClassStringType($objectType))->cast($classStringObject);
    }

    public function test_cast_invalid_class_string_of_union_type_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $unionType = new UnionType(new FakeObjectType(DateTime::class), new FakeObjectType(stdClass::class));
        $classStringObject = new StringableObject(DateTimeInterface::class);

        (new ClassStringType($unionType))->cast($classStringObject);
    }

    public function test_string_value_is_correct(): void
    {
        $objectType = new FakeObjectType();

        self::assertSame('class-string', (new ClassStringType())->toString());
        self::assertSame("class-string<{$objectType->toString()}>", (new ClassStringType($objectType))->toString());
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

    public function test_does_match_same_type_with_no_object_type(): void
    {
        self::assertTrue((new ClassStringType(new FakeObjectType()))->matches(new ClassStringType()));
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

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue((new ClassStringType())->matches(new ScalarConcreteType()));
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

    public function test_matches_default_array_key_type(): void
    {
        self::assertTrue((new ClassStringType())->matches(ArrayKeyType::default()));
    }

    public function test_matches_array_key_type_with_string_type(): void
    {
        self::assertTrue((new ClassStringType())->matches(ArrayKeyType::string()));
    }

    public function test_does_not_match_array_key_type_with_integer_type(): void
    {
        self::assertFalse((new ClassStringType())->matches(ArrayKeyType::integer()));
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subType = new FakeObjectType();

        $type = new ClassStringType($subType);

        self::assertSame([$subType], $type->traverse());
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('string', (new ClassStringType())->nativeType()->toString());
        self::assertSame('string', (new ClassStringType(new FakeObjectType()))->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
