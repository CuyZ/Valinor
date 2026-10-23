<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\ScalarConcreteType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function CuyZ\Valinor\Compiler\variable;

final class StringValueTypeTest extends UnitTestCase
{
    private StringValueType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->type = new StringValueType('Schwifty!');
    }

    public function test_value_can_be_retrieved(): void
    {
        self::assertSame('Schwifty!', $this->type->value());
    }

    #[TestWith(['Schwifty!'])]
    public function test_accepts_correct_values(mixed $value): void
    {
        $type = new StringValueType('Schwifty!');
        $typeSingleQuote = StringValueType::quoted("'Schwifty!'");
        $typeDoubleQuote = StringValueType::quoted('"Schwifty!"');

        self::assertTrue($type->accepts($value));
        self::assertTrue($typeSingleQuote->accepts($value));
        self::assertTrue($typeDoubleQuote->accepts($value));

        self::assertTrue($this->compiledAccept($type, $value));
        self::assertTrue($this->compiledAccept($typeSingleQuote, $value));
        self::assertTrue($this->compiledAccept($typeDoubleQuote, $value));
    }

    #[TestWith(['other string'])]
    #[TestWith([null])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        self::assertFalse($this->type->accepts($value));
        self::assertFalse($this->compiledAccept($this->type, $value));
    }

    public function test_string_value_is_correct(): void
    {
        $type = new StringValueType('Schwifty!');
        $typeSingleQuote = StringValueType::quoted("'Schwifty!'");
        $typeDoubleQuote = StringValueType::quoted('"Schwifty!"');

        self::assertSame('Schwifty!', $type->toString());
        self::assertSame("'Schwifty!'", $typeSingleQuote->toString());
        self::assertSame('"Schwifty!"', $typeDoubleQuote->toString());
    }

    public function test_matches_same_type_with_same_value(): void
    {
        $typeA = new StringValueType('Schwifty!');
        $typeB = new StringValueType('Schwifty!');
        $typeC = StringValueType::quoted("'Schwifty!'");
        $typeD = StringValueType::quoted('"Schwifty!"');

        self::assertTrue($typeA->matches($typeB));
        self::assertTrue($typeA->matches($typeC));
        self::assertTrue($typeA->matches($typeD));
    }

    public function test_starts_or_ends_with_quote(): void
    {
        $startsWithSimpleQuote = StringValueType::quoted("'Schwifty!");
        $endsWithSimpleQuote = StringValueType::quoted("Schwifty!'");
        $startsWithDoubleQuote = StringValueType::quoted('"Schwifty!');
        $endsWithDoubleQuote = StringValueType::quoted('Schwifty!"');

        self::assertSame('"\'Schwifty!"', $startsWithSimpleQuote->toString());
        self::assertSame('"Schwifty!\'"', $endsWithSimpleQuote->toString());
        self::assertSame("'\"Schwifty!'", $startsWithDoubleQuote->toString());
        self::assertSame("'Schwifty!\"'", $endsWithDoubleQuote->toString());
    }

    public function test_does_not_match_same_type_with_different_value(): void
    {
        $typeA = new StringValueType('Schwifty!');
        $typeB = new StringValueType('Schwifty?');

        self::assertFalse($typeA->matches($typeB));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->type->matches(new FakeType()));
    }

    public function test_matches_concrete_scalar_type(): void
    {
        self::assertTrue($this->type->matches(new ScalarConcreteType()));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->type->matches(new MixedType()));
    }

    public function test_matches_union_type_containing_string_type(): void
    {
        $unionType = new UnionType(
            new FakeType(),
            $this->type,
            new FakeType(),
        );

        self::assertTrue($this->type->matches($unionType));
    }

    public function test_does_not_match_union_type_not_containing_string_type(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        self::assertFalse($this->type->matches($unionType));
    }

    public function test_matches_default_array_key_type(): void
    {
        self::assertTrue($this->type->matches(ArrayKeyType::default()));
    }

    public function test_matches_array_key_type_with_string_type(): void
    {
        self::assertTrue($this->type->matches(ArrayKeyType::string()));
    }

    public function test_does_not_match_array_key_type_with_integer_type(): void
    {
        self::assertFalse($this->type->matches(ArrayKeyType::integer()));
    }

    public function test_native_type_is_correct(): void
    {
        self::assertSame('string', (new StringValueType('foo'))->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(variable('value'))->compile(new Compiler())->code() . ';');
    }
}
