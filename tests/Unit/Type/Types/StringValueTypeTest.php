<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use AssertionError;
use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\StringValueType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class StringValueTypeTest extends TestCase
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
        $typeSingleQuote = StringValueType::from("'Schwifty!'");
        $typeDoubleQuote = StringValueType::from('"Schwifty!"');

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

    public function test_can_cast_stringable_value(): void
    {
        self::assertTrue($this->type->canCast('Schwifty!'));
        self::assertTrue($this->type->canCast(new StringableObject('Schwifty!')));
    }

    public function test_cannot_cast_other_types(): void
    {
        self::assertFalse($this->type->canCast(404));
        self::assertFalse($this->type->canCast(42.1337));
        self::assertFalse($this->type->canCast(null));
        self::assertFalse($this->type->canCast(['foo' => 'bar']));
        self::assertFalse($this->type->canCast(false));
        self::assertFalse($this->type->canCast(new stdClass()));
    }

    #[DataProvider('cast_value_returns_correct_result_data_provider')]
    public function test_cast_value_returns_correct_result(StringValueType $type, mixed $value, string $expected): void
    {
        self::assertSame($expected, $type->cast($value));
    }

    public static function cast_value_returns_correct_result_data_provider(): array
    {
        return [
            'String from float' => [
                'type' => new StringValueType('404.42'),
                'value' => 404.42,
                'expected' => '404.42',
            ],
            'String from integer' => [
                'type' => new StringValueType('42'),
                'value' => 42,
                'expected' => '42',
            ],
            'String from object' => [
                'type' => new StringValueType('foo'),
                'value' => new StringableObject(),
                'expected' => 'foo',
            ],
            'String from string' => [
                'type' => new StringValueType('bar'),
                'value' => 'bar',
                'expected' => 'bar',
            ],
        ];
    }

    public function test_cast_another_string_value_throws_exception(): void
    {
        $this->expectException(AssertionError::class);

        $typeA = new StringValueType('Schwifty!');

        $typeA->cast(new StringableObject('Schwifty?'));
    }

    public function test_string_value_is_correct(): void
    {
        $type = new StringValueType('Schwifty!');
        $typeSingleQuote = StringValueType::from("'Schwifty!'");
        $typeDoubleQuote = StringValueType::from('"Schwifty!"');

        self::assertSame('Schwifty!', $type->toString());
        self::assertSame("'Schwifty!'", $typeSingleQuote->toString());
        self::assertSame('"Schwifty!"', $typeDoubleQuote->toString());
    }

    public function test_matches_same_type_with_same_value(): void
    {
        $typeA = new StringValueType('Schwifty!');
        $typeB = new StringValueType('Schwifty!');
        $typeC = StringValueType::from("'Schwifty!'");
        $typeD = StringValueType::from('"Schwifty!"');

        self::assertTrue($typeA->matches($typeB));
        self::assertTrue($typeA->matches($typeC));
        self::assertTrue($typeA->matches($typeD));
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

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
