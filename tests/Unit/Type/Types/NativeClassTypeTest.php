<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeCompositeType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class NativeClassTypeTest extends TestCase
{
    public function test_signature_can_be_retrieved(): void
    {
        $type = new NativeClassType(stdClass::class);

        self::assertSame(stdClass::class, $type->className());
    }

    public function test_string_value_is_signature(): void
    {
        $generic = new FakeType();
        $type = new NativeClassType(stdClass::class, ['Template' => $generic]);

        self::assertSame(stdClass::class . "<{$generic->toString()}>", $type->toString());
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue((new NativeClassType(stdClass::class))->accepts(new stdClass()));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        $type = new NativeClassType(stdClass::class);

        self::assertFalse($type->accepts(null));
        self::assertFalse($type->accepts('Schwifty!'));
        self::assertFalse($type->accepts(42.1337));
        self::assertFalse($type->accepts(404));
        self::assertFalse($type->accepts(['foo' => 'bar']));
        self::assertFalse($type->accepts(false));
        self::assertFalse($type->accepts(new DateTime()));
    }

    public function test_matches_other_identical_class(): void
    {
        $classTypeA = new NativeClassType(stdClass::class);
        $classTypeB = new NativeClassType(stdClass::class);

        self::assertTrue($classTypeA->matches($classTypeB));
    }

    public function test_matches_sub_class(): void
    {
        $classTypeA = new NativeClassType(DateTimeInterface::class);
        $classTypeB = new NativeClassType(DateTime::class);

        self::assertTrue($classTypeB->matches($classTypeA));
    }

    public function test_does_not_match_invalid_type(): void
    {
        self::assertFalse((new NativeClassType(stdClass::class))->matches(new FakeType()));
    }

    public function test_does_not_match_invalid_class(): void
    {
        $classTypeA = new NativeClassType(DateTimeInterface::class);
        $classTypeB = new NativeClassType(stdClass::class);

        self::assertFalse($classTypeA->matches($classTypeB));
    }

    public function test_matches_undefined_object_type(): void
    {
        self::assertTrue((new NativeClassType(stdClass::class))->matches(new UndefinedObjectType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new NativeClassType(stdClass::class))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new NativeClassType(stdClass::class),
            new FakeType(),
        );

        self::assertTrue((new NativeClassType(stdClass::class))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $classType = new NativeClassType(stdClass::class);
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($classType->matches($unionType));
    }

    public function test_traverse_type_yields_sub_types(): void
    {
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();

        $type = new NativeClassType(stdClass::class, [
            'TemplateA' => $subTypeA,
            'TemplateB' => $subTypeB,
        ]);

        self::assertCount(2, $type->traverse());
        self::assertContains($subTypeA, $type->traverse());
        self::assertContains($subTypeB, $type->traverse());
    }

    public function test_traverse_type_yields_types_recursively(): void
    {
        $subType = new FakeType();
        $compositeType = new FakeCompositeType($subType);

        $type = new NativeClassType(stdClass::class, ['Template' => $compositeType]);

        self::assertCount(2, $type->traverse());
        self::assertContains($subType, $type->traverse());
        self::assertContains($compositeType, $type->traverse());
    }
}
