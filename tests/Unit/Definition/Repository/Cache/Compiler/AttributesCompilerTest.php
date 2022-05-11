<?php

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Repository\Cache\Compiler\AttributesCompiler;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception\IncompatibleAttributes;
use CuyZ\Valinor\Tests\Fake\Definition\FakeNonEmptyAttributes;
use PHPUnit\Framework\TestCase;

final class AttributesCompilerTest extends TestCase
{
    public function test_incompatible_attributes_class_throws_exception(): void
    {
        $this->expectException(IncompatibleAttributes::class);
        $this->expectExceptionCode(1_616_925_611);
        $this->expectExceptionMessage('The Attributes class of type `' . FakeNonEmptyAttributes::class . '` cannot be compiled.');

        (new AttributesCompiler())->compile(new FakeNonEmptyAttributes());
    }
}
