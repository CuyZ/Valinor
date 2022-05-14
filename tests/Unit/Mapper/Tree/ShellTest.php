<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotGetParentOfRootShell;
use CuyZ\Valinor\Mapper\Tree\Exception\NewShellTypeDoesNotMatch;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Tests\Fake\Definition\FakeAttributes;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use PHPUnit\Framework\TestCase;

final class ShellTest extends TestCase
{
    public function test_type_and_value_can_be_retrieved(): void
    {
        $type = new FakeType();
        $value = 'foo';

        $shell = Shell::root($type, $value);

        self::assertSame($type, $shell->type());
        self::assertSame($value, $shell->value());
    }

    public function test_change_type_changes_type(): void
    {
        $typeA = FakeType::matching($typeB = new FakeType());

        $shellA = Shell::root($typeA, []);
        $shellB = $shellA->withType($typeB);

        self::assertNotSame($shellA, $shellB);
        self::assertSame($typeB, $shellB->type());
    }

    public function test_unresolvable_type_throws_exception(): void
    {
        $type = new UnresolvableType('some message');

        $this->expectException(UnresolvableShellType::class);
        $this->expectExceptionCode(1630943848);
        $this->expectExceptionMessage('some message');

        Shell::root($type, []);
    }

    public function test_change_type_with_invalid_type_throws_exception(): void
    {
        $typeA = new FakeType();
        $typeB = new FakeType();

        $this->expectException(NewShellTypeDoesNotMatch::class);
        $this->expectExceptionCode(1628845224);
        $this->expectExceptionMessage("Trying to change the type of the shell at path ``: `$typeB` is not a valid subtype of `$typeA`.");

        (Shell::root($typeA, []))->withType($typeB);
    }

    public function test_change_value_changes_value(): void
    {
        $valueA = 'foo';
        $valueB = 'bar';

        $shellA = Shell::root(new FakeType(), $valueA);
        $shellB = $shellA->withValue($valueB);

        self::assertNotSame($shellA, $shellB);
        self::assertSame($valueB, $shellB->value());
    }

    public function test_root_shell_is_root(): void
    {
        $shell = Shell::root(new FakeType(), []);

        self::assertTrue($shell->isRoot());
        self::assertSame('', $shell->name());
    }

    public function test_get_root_shell_parent_throws_exception(): void
    {
        $this->expectException(CannotGetParentOfRootShell::class);
        $this->expectExceptionCode(1630674894);
        $this->expectExceptionMessage('Impossible to get the parent of a root shell.');

        $shell = Shell::root(new FakeType(), []);
        $shell->parent();
    }

    public function test_shell_child_values_can_be_retrieved(): void
    {
        $value = 'some value';
        $type = FakeType::permissive();
        $attributes = new FakeAttributes();

        $shell = Shell::root(new FakeType(), []);
        $child = $shell->child('foo', $type, $value, $attributes);

        self::assertSame('foo', $child->name());
        self::assertSame('foo', $child->path());
        self::assertSame($type, $child->type());
        self::assertSame($value, $child->value());
        self::assertSame($attributes, $child->attributes());
    }
}
