<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\IntersectionType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Iterator;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class InterfaceTypeTest extends TestCase
{
    public function test_signature_can_be_retrieved(): void
    {
        $type = new InterfaceType(DateTimeInterface::class);

        self::assertSame(DateTimeInterface::class, $type->className());
    }

    public function test_string_value_is_correct(): void
    {
        $generic = new FakeType();
        $type = new InterfaceType(stdClass::class, ['Template' => $generic]);

        self::assertSame(stdClass::class . "<{$generic->toString()}>", $type->toString());
    }

    #[TestWith([new DateTime()])]
    #[TestWith([new DateTimeImmutable()])]
    public function test_accepts_correct_values(mixed $value): void
    {
        $type = new InterfaceType(DateTimeInterface::class);

        self::assertTrue($type->accepts($value));
        self::assertTrue($this->compiledAccept($type, $value));
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
        $type = new InterfaceType(DateTimeInterface::class);

        self::assertFalse($type->accepts($value));
        self::assertFalse($this->compiledAccept($type, $value));
    }

    public function test_matches_other_identical_interface(): void
    {
        $interfaceTypeA = new InterfaceType(DateTimeInterface::class);
        $interfaceTypeB = new InterfaceType(DateTimeInterface::class);

        self::assertTrue($interfaceTypeA->matches($interfaceTypeB));
    }

    public function test_matches_sub_class(): void
    {
        $interfaceTypeA = new InterfaceType(SomeChildInterface::class);
        $interfaceTypeB = new InterfaceType(SomeParentInterface::class);

        self::assertTrue($interfaceTypeA->matches($interfaceTypeB));
    }

    public function test_does_not_match_invalid_type(): void
    {
        self::assertFalse((new InterfaceType(DateTimeInterface::class))->matches(new FakeType()));
    }

    public function test_does_not_match_invalid_class(): void
    {
        $interfaceTypeA = new InterfaceType(DateTimeInterface::class);
        $interfaceTypeB = new InterfaceType(Iterator::class);

        self::assertFalse($interfaceTypeA->matches($interfaceTypeB));
    }

    public function test_matches_undefined_object_type(): void
    {
        self::assertTrue((new InterfaceType(DateTimeInterface::class))->matches(new UndefinedObjectType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue((new InterfaceType(DateTimeInterface::class))->matches(new MixedType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new InterfaceType(DateTimeInterface::class),
            new FakeType(),
        );

        self::assertTrue((new InterfaceType(DateTimeInterface::class))->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $interfaceType = new InterfaceType(DateTimeInterface::class);
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($interfaceType->matches($unionType));
    }

    public function test_matches_intersection_of_valid_types(): void
    {
        $intersectionType = new IntersectionType(
            new InterfaceType(SomeParentInterface::class),
            new InterfaceType(SomeOtherParentInterface::class),
        );

        self::assertTrue((new InterfaceType(SomeChildInterface::class))->matches($intersectionType));
    }

    public function test_does_not_match_intersection_containing_invalid_type(): void
    {
        $intersectionType = new IntersectionType(
            new FakeObjectType(stdClass::class),
            new FakeObjectType(stdClass::class)
        );

        self::assertFalse((new InterfaceType(DateTime::class))->matches($intersectionType));
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}

interface SomeParentInterface {}

interface SomeOtherParentInterface {}

interface SomeChildInterface extends SomeParentInterface, SomeOtherParentInterface {}
