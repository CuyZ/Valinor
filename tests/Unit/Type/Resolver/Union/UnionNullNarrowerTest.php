<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Type\Resolver\Union;

use CuyZ\Valinor\Tests\Fake\Type\FakeObjectType;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Tests\Fake\Type\Resolver\Union\FakeUnionNarrower;
use CuyZ\Valinor\Type\Resolver\Exception\UnionTypeDoesNotAllowNull;
use CuyZ\Valinor\Type\Resolver\Union\UnionNullNarrower;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\UnionType;
use PHPUnit\Framework\TestCase;

final class UnionNullNarrowerTest extends TestCase
{
    private UnionNullNarrower $unionNullNarrower;

    private FakeUnionNarrower $delegate;

    protected function setUp(): void
    {
        parent::setUp();

        $this->delegate = new FakeUnionNarrower();

        $this->unionNullNarrower = new UnionNullNarrower($this->delegate);
    }

    public function test_null_value_with_union_type_allowing_null_returns_null_type(): void
    {
        $unionType = new UnionType(new FakeType(), NullType::get());

        $type = $this->unionNullNarrower->narrow($unionType, null);

        self::assertInstanceOf(NullType::class, $type);
    }

    public function test_union_not_containing_null_type_is_narrowed_by_delegate(): void
    {
        $type = new FakeType();
        $this->delegate->willReturn($type);
        $unionType = new UnionType(new FakeType(), new FakeType());

        $narrowedType = $this->unionNullNarrower->narrow($unionType, 'foo');

        self::assertSame($type, $narrowedType);
    }

    public function test_null_value_not_allowed_by_union_type_throws_exception(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeType());

        $this->expectException(UnionTypeDoesNotAllowNull::class);
        $this->expectExceptionCode(1618742357);
        $this->expectExceptionMessage("Cannot be empty and must be filled with a value matching `$unionType`.");

        $this->unionNullNarrower->narrow($unionType, null);
    }

    public function test_null_value_not_allowed_by_union_type_containing_object_type_throws_exception(): void
    {
        $unionType = new UnionType(new FakeType(), new FakeObjectType());

        $this->expectException(UnionTypeDoesNotAllowNull::class);
        $this->expectExceptionCode(1618742357);
        $this->expectExceptionMessage('Cannot be empty.');

        $this->unionNullNarrower->narrow($unionType, null);
    }

    public function test_non_null_value_for_union_type_with_null_type_at_left_returns_type_at_right(): void
    {
        $type = new FakeType();
        $unionType = new UnionType($type, new NullType());

        $narrowedType = $this->unionNullNarrower->narrow($unionType, 'foo');

        self::assertSame($type, $narrowedType);
    }

    public function test_non_null_value_for_union_type_with_null_type_at_right_returns_type_at_left(): void
    {
        $type = new FakeType();
        $unionType = new UnionType(new NullType(), $type);

        $narrowedType = $this->unionNullNarrower->narrow($unionType, 'foo');

        self::assertSame($type, $narrowedType);
    }
}
