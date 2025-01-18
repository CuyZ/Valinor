<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Types;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Traits\TestIsSingleton;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use stdClass;

final class MixedTypeTest extends TestCase
{
    use TestIsSingleton;

    private MixedType $mixedType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mixedType = new MixedType();
    }

    public function test_string_value_is_correct(): void
    {
        self::assertSame('mixed', $this->mixedType->toString());
    }

    #[TestWith([null])]
    #[TestWith(['Schwifty!'])]
    #[TestWith([42.1337])]
    #[TestWith([404])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([false])]
    #[TestWith([new stdClass()])]
    public function test_accepts_every_value(mixed $value): void
    {
        self::assertTrue($this->mixedType->accepts($value));
        self::assertTrue($this->compiledAccept($this->mixedType, $value));
    }

    public function test_matches_same_type(): void
    {
        self::assertTrue((new MixedType())->matches(new MixedType()));
    }

    public function test_does_not_match_other_type(): void
    {
        self::assertFalse($this->mixedType->matches(new FakeType()));
    }

    private function compiledAccept(Type $type, mixed $value): bool
    {
        /** @var bool */
        return eval('return ' . $type->compiledAccept(Node::variable('value'))->compile(new Compiler())->code() . ';');
    }
}
