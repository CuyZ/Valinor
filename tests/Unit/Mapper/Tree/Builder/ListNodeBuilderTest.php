<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Library\Settings;
use CuyZ\Valinor\Mapper\Tree\Builder\ListNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use CuyZ\Valinor\Type\Types\ListType;
use PHPUnit\Framework\TestCase;

final class ListNodeBuilderTest extends TestCase
{
    public function test_build_with_null_value_in_flexible_mode_returns_empty_branch_node(): void
    {
        $setting = new Settings();
        $setting->enableFlexibleCasting = true;

        $shell = FakeShell::new(ListType::native(), settings: $setting);

        $node = (new RootNodeBuilder(new ListNodeBuilder()))->build($shell);

        self::assertSame([], $node->value());
        self::assertEmpty($node->node()->children());
    }

    public function test_invalid_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        (new RootNodeBuilder(new ListNodeBuilder()))->build(FakeShell::new(new FakeType()));
    }
}
