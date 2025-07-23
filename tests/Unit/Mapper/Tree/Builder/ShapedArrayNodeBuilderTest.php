<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use AssertionError;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\ShapedArrayNodeBuilder;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use PHPUnit\Framework\TestCase;

final class ShapedArrayNodeBuilderTest extends TestCase
{
    public function test_invalid_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        (new RootNodeBuilder(new ShapedArrayNodeBuilder()))->build(FakeShell::any());
    }
}
