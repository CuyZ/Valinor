<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Unit\Definition\Repository\Cache\Compiler;

use AssertionError;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\FunctionDefinitionCompiler;
use PHPUnit\Framework\TestCase;
use stdClass;

final class FunctionDefinitionCompilerTest extends TestCase
{
    public function test_compile_wrong_type_fails_assertion(): void
    {
        $this->expectException(AssertionError::class);

        (new FunctionDefinitionCompiler())->compile(new stdClass());
    }
}
