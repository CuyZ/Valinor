<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\FunctionDefinitionCompiler;
use CuyZ\Valinor\Type\Types\NativeStringType;
use Error;
use PHPUnit\Framework\TestCase;

final class FunctionDefinitionCompilerTest extends TestCase
{
    private FunctionDefinitionCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new FunctionDefinitionCompiler();
    }

    public function test_function_is_compiled_correctly(): void
    {
        $function = new FunctionDefinition(
            'foo',
            'foo:42-1337',
            new Parameters(
                new ParameterDefinition('bar', 'foo::bar', NativeStringType::get(), false, 'foo', EmptyAttributes::get())
            ),
            NativeStringType::get()
        );

        $code = $this->compiler->compile($function);
        $compiledFunction = $this->eval($code);

        self::assertSame('foo', $compiledFunction->name());
        self::assertSame('foo:42-1337', $compiledFunction->signature());
        self::assertTrue($compiledFunction->parameters()->has('bar'));
        self::assertInstanceOf(NativeStringType::class, $compiledFunction->returnType());
    }

    private function eval(string $code): FunctionDefinition
    {
        try {
            return eval("return $code;");
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }
    }
}
