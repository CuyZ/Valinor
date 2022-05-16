<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler;

use AssertionError;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use stdClass;

final class ClassDefinitionCompilerTest extends TestCase
{
    private ClassDefinitionCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new ClassDefinitionCompiler(true);
    }

    public function test_compile_wrong_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        $this->compiler->compile(new stdClass());
    }

    public function test_compile_validation_for_wrong_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        $this->compiler->compileValidation(new stdClass());
    }

    public function test_compile_validation_always_returns_true_when_source_validation_is_disabled(): void
    {
        $compiler = new ClassDefinitionCompiler(false);
        $classDefinition = FakeClassDefinition::fromReflection(new ReflectionClass(new class () {}));
        self::assertSame('true', $compiler->compileValidation($classDefinition));
    }
}
