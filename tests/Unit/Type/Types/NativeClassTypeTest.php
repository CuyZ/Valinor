<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\TestWith;
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

    #[TestWith([new stdClass()])]
    public function test_accepts_correct_values(mixed $value): void
    {
        $type = new NativeClassType(stdClass::class);

        self::assertTrue($type->accepts($value));
        self::assertTrue($this->compiledAccept($type, $value));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new DateTime()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        $type = new NativeClassType(stdClass::class);

        self::assertFalse($type->accepts($value));
        self::assertFalse($this->compiledAccept($type, $value));
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

    public function test_traverse_type_yields_generic_types(): void
    {
        $subTypeA = new FakeType();
        $subTypeB = new FakeType();

        $type = new NativeClassType(stdClass::class, ['T1' => $subTypeA, 'T2' => $subTypeB]);

        self::assertSame([$subTypeA, $subTypeB], $type->traverse());
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame(stdClass::class, (new NativeClassType(stdClass::class))->nativeType()->toString());
        self::assertSame(stdClass::class, (new NativeClassType(stdClass::class, ['Template' => new FakeType()]))->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
