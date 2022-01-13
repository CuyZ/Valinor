<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassTypeTest extends TestCase
{
    public function test_signature_can_be_retrieved(): void
    {
        $type = new ClassType(stdClass::class);

        self::assertSame(stdClass::class, $type->className());
    }

    public function test_string_value_is_signature(): void
    {
        $generic = new FakeType();
        $type = new ClassType(stdClass::class, ['Template' => $generic]);

        self::assertSame(stdClass::class . "<$generic>", (string)$type);
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue((new ClassType(stdClass::class))->accepts(new stdClass()));
    }

    public function test_does_not_accept_incorrect_values(): void
    {
        $type = new ClassType(stdClass::class);

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
        $classTypeA = new ClassType(stdClass::class);
        $classTypeB = new ClassType(stdClass::class);

        self::assertTrue($classTypeA->matches($classTypeB));
    }

    public function test_matches_sub_class(): void
    {
        $classTypeA = new ClassType(DateTimeInterface::class);
        $classTypeB = new ClassType(DateTime::class);

        self::assertTrue($classTypeB->matches($classTypeA));
    }

    public function test_does_not_match_invalid_type(): void
    {
        self::assertFalse((new ClassType(stdClass::class))->matches(new FakeType()));
    }

    public function test_does_not_match_invalid_class(): void
    {
        $classTypeA = new ClassType(DateTimeInterface::class);
        $classTypeB = new ClassType(stdClass::class);

        self::assertFalse($classTypeA->matches($classTypeB));
    }

    public function test_matches_undefined_object_type(): void
    {
        self::assertTrue((new ClassType(stdClass::class))->matches(new UndefinedObjectType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new ClassType(stdClass::class))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new ClassType(stdClass::class),
            new FakeType(),
        );

        self::assertTrue((new ClassType(stdClass::class))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $classType = new ClassType(stdClass::class);
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($classType->matches($unionType));
    }
}
