<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Mapper\Tree\Builder\ArrayNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ArrayType;
use PHPUnit\Framework\TestCase;

final class ArrayNodeBuilderTest extends TestCase
{
    public function test_build_with_null_value_in_flexible_mode_returns_empty_branch_node(): void
    {
        $node = (new RootNodeBuilder(new ArrayNodeBuilder(true)))->build(FakeShell::new(ArrayType::native()));

        self::assertSame([], $node->value());
        self::assertEmpty($node->node()->children());
    }

    public function test_invalid_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        (new RootNodeBuilder(new ArrayNodeBuilder(true)))->build(FakeShell::new(new FakeType()));
    }
}
