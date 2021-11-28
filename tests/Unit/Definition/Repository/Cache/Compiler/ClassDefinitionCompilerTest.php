<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler;

use AssertionError;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ClassDefinitionCompilerTest extends TestCase
{
    private ClassDefinitionCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new ClassDefinitionCompiler();
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
}
