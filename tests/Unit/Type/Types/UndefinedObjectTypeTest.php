<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

final class UndefinedObjectTypeTest extends UnitTestCase
{
    use TestIsSingleton;

    private UndefinedObjectType $undefinedObjectType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->undefinedObjectType = new UndefinedObjectType();
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('object', $this->undefinedObjectType->toString());
    }

    public function test_accepts_correct_values(): void
    {
        self::assertTrue($this->undefinedObjectType->accepts(new stdClass()));
        self::assertTrue($this->undefinedObjectType->accepts(new class () {}));

        self::assertTrue($this->compiledAccept($this->undefinedObjectType, new stdClass()));
        self::assertTrue($this->compiledAccept($this->undefinedObjectType, new class () {}));
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->undefinedObjectType->accepts($value));
        self::assertFalse($this->compiledAccept($this->undefinedObjectType, $value));
    }

    public function test_matches_valid_types(): void
    {
        self::assertTrue($this->undefinedObjectType->matches(new UndefinedObjectType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->undefinedObjectType->matches(new FakeType()));
    }

    public function test_matches_union_containing_valid_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            new UndefinedObjectType(),
            new FakeType(),
        );

        self::assertTrue($this->undefinedObjectType->matches($unionType));
    }

    public function test_does_not_match_union_containing_invalid_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->undefinedObjectType->matches($unionType));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->undefinedObjectType->matches(new MixedType()));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('object', (new UndefinedObjectType())->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
