<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Builder\CasterNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\NoCasterForType;
use CuyZ\Valinor\Tests\Fake\Mapper\FakeShell;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class CasterNodeBuilderTest extends TestCase
{
    public function test_no_caster_found_throws_exception(): void
    {
        $type = new FakeType();

        $this->expectException(NoCasterForType::class);
        $this->expectExceptionCode(1_630_693_475);
        $this->expectExceptionMessage("No caster was found to convert to type `$type`.");

        (new RootNodeBuilder(new CasterNodeBuilder([])))->build(FakeShell::new($type));
    }
}
