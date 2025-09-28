<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\GenericType;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class GenericTypeTest extends TestCase
{
    #[TestWith(['foo'])]
    public function test_accepts_correct_values(mixed $value): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertTrue($genericType->accepts($value));
        self::assertTrue($this->compiledAccept($genericType, $value));
    }

    #[TestWith([null])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_does_not_accept_incorrect_values(mixed $value): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertFalse($genericType->accepts($value));
        self::assertFalse($this->compiledAccept($genericType, $value));

    }

    public function test_string_value_is_correct(): void
    {
        $genericTypeOfMixed = new GenericType('T', new MixedType());
        $genericTypeOfString = new GenericType('T', new NativeStringType());

        self::assertSame('T', $genericTypeOfMixed->toString());

        self::assertSame('T of string', $genericTypeOfString->toString());

    }

    public function test_matches_same_type(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertTrue($genericType->matches(new NativeStringType()));
    }

    public function test_does_not_match_other_type(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertFalse($genericType->matches(new FakeType()));
    }

    public function test_native_type_is_correct(): void
    {
        $genericType = new GenericType('T', new NativeStringType());

        self::assertSame('string', $genericType->nativeType()->toString());
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
