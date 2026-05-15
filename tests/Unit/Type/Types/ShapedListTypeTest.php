<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Unit\UnitTestCase;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\InvalidShapedListUnsealedType;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListInvalidKey;
use CuyZ\Valinor\Type\Parser\Exception\Iterable\ShapedListMandatoryAfterOptionalElement;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ArrayType;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\IntegerValueType;
use CuyZ\Valinor\Type\Types\ListType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedListType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;

use function array_keys;

final class ShapedListTypeTest extends UnitTestCase
{
    /** @var ShapedArrayElement[] */
    private array $elements;

    private ListType $unsealedType;

    private ShapedListType $type;

    protected function setUp(): void
    {
        parent::setUp();

        $this->elements = [
            0 => new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
            1 => new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType(), true),
        ];
        $this->unsealedType = new ListType(new NativeFloatType());

        $this->type = new ShapedListType(elements: $this->elements, isUnsealed: true, unsealedType: $this->unsealedType);
    }

    public function test_shape_properties_can_be_retrieved(): void
    {
        self::assertSame($this->unsealedType, $this->type->unsealedType());
        self::assertSame($this->elements, $this->type->elements);
    }

    public function test_invalid_unsealed_type_throws_exception(): void
    {
        $this->expectException(InvalidShapedListUnsealedType::class);
        $this->expectExceptionMessage('Invalid unsealed type in shaped list');

        ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
            ],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::default(), new NativeStringType()),
        );
    }

    public function test_invalid_unsealed_type_with_optional_elements_throws_exact_exception_message(): void
    {
        $this->expectException(InvalidShapedListUnsealedType::class);
        $this->expectExceptionMessage('Invalid unsealed type in shaped list `list{0: string, 1?: int, ...array<string>}`, it should be a valid list but `array<string>` was given.');

        ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType(), true),
            ],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::default(), new NativeStringType()),
        );
    }

    public function test_invalid_unsealed_type_with_required_elements_throws_exact_exception_message(): void
    {
        $this->expectException(InvalidShapedListUnsealedType::class);
        $this->expectExceptionMessage('Invalid unsealed type in shaped list `list{string, ...array<string>}`, it should be a valid list but `array<string>` was given.');

        ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
            ],
            isUnsealed: true,
            unsealedType: new ArrayType(ArrayKeyType::default(), new NativeStringType()),
        );
    }

    public function test_mandatory_after_optional_throws_exception(): void
    {
        $this->expectException(ShapedListMandatoryAfterOptionalElement::class);
        $this->expectExceptionMessage('Mandatory element at position 1 cannot follow an optional element in shaped list `list{0?: string, 1: int}`.');

        ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType(), true),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType(), false),
            ],
            isUnsealed: false,
        );
    }

    public function test_mandatory_after_optional_with_required_prefix_throws_exact_exception_message(): void
    {
        $this->expectException(ShapedListMandatoryAfterOptionalElement::class);
        $this->expectExceptionMessage('Mandatory element at position 2 cannot follow an optional element in shaped list `list{0: string, 1?: int, 2: float}`.');

        ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType(), true),
                new ShapedArrayElement(new IntegerValueType(2), new NativeFloatType(), false),
            ],
            isUnsealed: false,
        );
    }

    public function test_invalid_explicit_key_throws_exception(): void
    {
        $this->expectException(ShapedListInvalidKey::class);
        $this->expectExceptionMessage('Key `2` is not valid for a list element; expected sequential integer key `0`');

        ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(2), new NativeStringType()),
            ],
            isUnsealed: false,
        );
    }

    public function test_invalid_explicit_key_with_preceding_optional_throws_exact_exception_message(): void
    {
        $this->expectException(ShapedListInvalidKey::class);
        $this->expectExceptionMessage('Key `2` is not valid for a list element; expected sequential integer key `1` in shaped list `list{0?: string, 2:...}`.');

        ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType(), true),
                new ShapedArrayElement(new IntegerValueType(2), new NativeIntegerType()),
            ],
            isUnsealed: false,
        );
    }

    public function test_default_is_sealed(): void
    {
        $type = new ShapedListType(elements: []);
        self::assertFalse($type->isUnsealed);
        self::assertSame('list{}', $type->toString());
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('list{0: string, 1?: int, ...list<float>}', $this->type->toString());
    }

    public function test_string_value_for_required_only_list_is_positional(): void
    {
        $type = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType()),
            ],
            isUnsealed: false,
        );
        self::assertSame('list{string, int}', $type->toString());
    }

    public function test_string_value_for_empty_sealed_list(): void
    {
        $type = new ShapedListType(elements: [], isUnsealed: false);
        self::assertSame('list{}', $type->toString());
    }

    public function test_string_value_for_unsealed_without_typed_rest(): void
    {
        $type = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: true,
        );
        self::assertSame('list{string, ...}', $type->toString());
    }

    // Sealed — valid values
    #[TestWith([['foo']])]
    #[TestWith([['foo', 42]])]
    public function test_accepts_correct_values_sealed(mixed $value): void
    {
        $type = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType(), true),
            ],
            isUnsealed: false,
        );

        self::assertTrue($type->accepts($value));
        self::assertTrue($this->compiledAccept($type, $value));
    }

    // Unsealed — valid
    #[TestWith([['foo']])]
    #[TestWith([['foo', 42]])]
    #[TestWith([['foo', 42, 1.5, 2.5]])]
    public function test_accepts_correct_values_unsealed(mixed $value): void
    {
        self::assertTrue($this->type->accepts($value));
        self::assertTrue($this->compiledAccept($this->type, $value));
    }

    // Not a list (non-sequential keys) — invalid
    #[TestWith([[1 => 'foo']])]
    #[TestWith([['key' => 'foo']])]
    // Missing required element
    #[TestWith([[]])]
    // Wrong type at required position
    #[TestWith([[42]])]
    // Wrong type at optional position
    #[TestWith([['foo', 'not-an-int']])]
    // Extra elements for sealed
    #[TestWith([['foo', 42, 'extra']])]
    // Non-array
    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values_sealed(mixed $value): void
    {
        $type = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType(), true),
            ],
            isUnsealed: false,
        );

        self::assertFalse($type->accepts($value));
        self::assertFalse($this->compiledAccept($type, $value));
    }

    #[TestWith([[42]])]
    #[TestWith([['foo', 42, 'not-float']])]
    #[TestWith([[1 => 'foo']])]
    #[TestWith([null])]
    public function test_does_not_accept_incorrect_values_unsealed(mixed $value): void
    {
        self::assertFalse($this->type->accepts($value));
        self::assertFalse($this->compiledAccept($this->type, $value));
    }

    public function test_does_not_accept_non_list_when_only_element_is_optional(): void
    {
        $type = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType(), true)],
            isUnsealed: false,
        );

        self::assertFalse($type->accepts([1 => 'foo']));
        self::assertFalse($this->compiledAccept($type, [1 => 'foo']));
    }

    public function test_does_not_accept_value_when_unsealed_type_is_vacant(): void
    {
        $type = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: true,
            unsealedType: new GenericType('T', new NativeStringType()),
        );

        self::assertFalse($type->accepts(['foo']));
        self::assertFalse($this->compiledAccept($type, ['foo']));
    }

    public function test_matches_valid_shaped_list_type(): void
    {
        $otherA = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType(), true),
            ],
            isUnsealed: true,
            unsealedType: new ListType(new NativeFloatType()),
        );

        $otherB = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: true,
        );

        self::assertTrue($this->type->matches($otherA));
        self::assertTrue($this->type->matches($otherB));
    }

    public function test_matches_list_type(): void
    {
        $type = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: false,
        );

        self::assertTrue($type->matches(ListType::native()));
        self::assertTrue($type->matches(new ListType(new NativeStringType())));
    }

    public function test_does_not_match_list_type_when_element_type_does_not_match(): void
    {
        $type = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeIntegerType())],
            isUnsealed: false,
        );

        self::assertFalse($type->matches(new ListType(new NativeStringType())));
    }

    public function test_unsealed_does_not_match_list_type_when_unsealed_type_does_not_match(): void
    {
        $type = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: true,
            unsealedType: new ListType(new NativeIntegerType()),
        );

        self::assertFalse($type->matches(new ListType(new NativeStringType())));
    }

    public function test_matches_mixed_type(): void
    {
        self::assertTrue($this->type->matches(MixedType::get()));
    }

    public function test_unsealed_matches_sealed_shaped_list(): void
    {
        $sealedOther = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: false,
        );

        self::assertTrue($this->type->matches($sealedOther));
    }

    public function test_sealed_does_not_match_unsealed_shaped_list(): void
    {
        $sealedType = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: false,
        );

        $unsealedOther = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: true,
        );

        self::assertFalse($sealedType->matches($unsealedOther));
    }

    public function test_does_not_match_unsealed_shaped_list_with_incompatible_unsealed_type(): void
    {
        $incompatibleUnsealed = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: true,
            unsealedType: new ListType(new NativeIntegerType()),
        );

        self::assertFalse($this->type->matches($incompatibleUnsealed));
    }

    public function test_does_not_match_shaped_list_where_first_element_matches_but_second_does_not(): void
    {
        $type = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType()),
            ],
            isUnsealed: false,
        );

        $other = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeFloatType()),
            ],
            isUnsealed: false,
        );

        self::assertFalse($type->matches($other));
    }

    public function test_does_not_match_string_keyed_array_type(): void
    {
        self::assertFalse($this->type->matches(new ArrayType(ArrayKeyType::string(), MixedType::get())));
    }

    public function test_sealed_list_does_not_match_string_keyed_array_type(): void
    {
        $type = ShapedListType::from(
            elements: [new ShapedArrayElement(new IntegerValueType(0), new NativeStringType())],
            isUnsealed: false,
        );

        self::assertFalse($type->matches(new ArrayType(ArrayKeyType::string(), new NativeStringType())));
    }

    public function test_does_not_match_fake_type(): void
    {
        self::assertFalse($this->type->matches(new FakeType()));
    }

    public function test_matches_union_containing_itself(): void
    {
        $union = new UnionType($this->type, new NativeStringType());

        self::assertTrue($this->type->matches($union));
    }

    public function test_does_not_infer_generics_from_non_shaped_list_type(): void
    {
        $generics = $this->type->inferGenericsFrom(new NativeStringType(), new Generics());

        self::assertSame([], $generics->items);
    }

    public function test_infers_generics_from_matching_shaped_list_elements(): void
    {
        $type = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new GenericType('T', new NativeStringType())),
                new ShapedArrayElement(new IntegerValueType(1), new GenericType('U', new NativeIntegerType())),
            ],
            isUnsealed: false,
        );

        $other = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType()),
            ],
            isUnsealed: false,
        );

        $generics = $type->inferGenericsFrom($other, new Generics());

        self::assertSame(['T', 'U'], array_keys($generics->items));
        self::assertSame('string', $generics->items['T']->toString());
        self::assertSame('int', $generics->items['U']->toString());
    }

    public function test_infer_generics_skips_missing_keys_and_keeps_later_matches(): void
    {
        $type = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new GenericType('T', new NativeStringType())),
                new ShapedArrayElement(new IntegerValueType(1), new GenericType('U', new NativeIntegerType())),
            ],
            isUnsealed: false,
        );

        $other = new ShapedListType(
            elements: [
                1 => new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType()),
            ],
            isUnsealed: false,
        );

        $generics = $type->inferGenericsFrom($other, new Generics());

        self::assertSame(['U'], array_keys($generics->items));
        self::assertSame('int', $generics->items['U']->toString());
    }

    public function test_native_type_is_list(): void
    {
        self::assertSame('list', $this->type->nativeType()->toString());
    }

    public function test_traverse_returns_element_types(): void
    {
        $traversed = $this->type->traverse();

        self::assertContains($this->elements[0]->type(), $traversed);
        self::assertContains($this->elements[1]->type(), $traversed);
        self::assertContains($this->unsealedType, $traversed);
    }

    public function test_replace_returns_new_instance_with_replaced_types(): void
    {
        $sealed = ShapedListType::from(
            elements: [
                new ShapedArrayElement(new IntegerValueType(0), new NativeStringType()),
                new ShapedArrayElement(new IntegerValueType(1), new NativeIntegerType()),
            ],
            isUnsealed: false,
        );

        $replaced = $sealed->replace(static fn () => new NativeFloatType());

        self::assertInstanceOf(ShapedListType::class, $replaced);
        self::assertNotSame($sealed, $replaced);

        foreach ($replaced->elements as $element) {
            self::assertInstanceOf(NativeFloatType::class, $element->type());
        }
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
