<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\Repository\Cache\Compiler\AttributesCompiler;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\Exception\TypeCannotBeCompiled;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\TypeCompiler;
use CuyZ\Valinor\Tests\Fake\Type\FakeType;
use PHPUnit\Framework\TestCase;

final class TypeCompilerTest extends TestCase
{
    private TypeCompiler $typeCompiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typeCompiler = new TypeCompiler(new AttributesCompiler(new ClassDefinitionCompiler()));
    }

    public function test_invalid_compiled_type_throws_exception(): void
    {
        $this->expectException(TypeCannotBeCompiled::class);
        $this->expectExceptionMessage('The type `' . FakeType::class . '` cannot be compiled.');

        $this->typeCompiler->compile(new FakeType());
    }
}
