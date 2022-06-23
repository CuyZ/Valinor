<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Mapper\Tree\Builder\EnumNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidEnumValue;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedIntegerEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\BackedStringEnum;
use CuyZ\Valinor\Tests\Fixture\Enum\PureEnum;
use CuyZ\Valinor\Type\Types\EnumType;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @requires PHP >= 8.1
 */
final class EnumNodeBuilderTest extends TestCase
{
    private RootNodeBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new RootNodeBuilder(new EnumNodeBuilder(true));
    }

    public function test_invalid_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        $this->builder->build(FakeShell::any());
    }

    public function test_invalid_value_throws_exception(): void
    {
        $type = new EnumType(PureEnum::class);

        $this->expectException(InvalidEnumValue::class);
        $this->expectExceptionCode(1633093113);
        $this->expectExceptionMessage("Value 'foo' does not match any of 'FOO', 'BAR'.");

        $this->builder->build(FakeShell::new($type, 'foo'));
    }

    public function test_invalid_string_value_throws_exception(): void
    {
        $type = new EnumType(BackedStringEnum::class);

        $this->expectException(InvalidEnumValue::class);
        $this->expectExceptionCode(1633093113);
        $this->expectExceptionMessage("Value object(stdClass) does not match any of 'foo', 'bar'.");

        $this->builder->build(FakeShell::new($type, new stdClass()));
    }

    public function test_boolean_instead_of_integer_value_throws_exception(): void
    {
        $type = new EnumType(BackedIntegerEnum::class);

        $this->expectException(InvalidEnumValue::class);
        $this->expectExceptionCode(1633093113);
        $this->expectExceptionMessage('Value false does not match any of 42, 1337.');

        $this->builder->build(FakeShell::new($type, false));
    }

    public function test_invalid_integer_value_throws_exception(): void
    {
        $type = new EnumType(BackedIntegerEnum::class);

        $this->expectException(InvalidEnumValue::class);
        $this->expectExceptionCode(1633093113);
        $this->expectExceptionMessage('Value object(stdClass) does not match any of 42, 1337.');

        $this->builder->build(FakeShell::new($type, new stdClass()));
    }
}
