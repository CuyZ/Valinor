<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\EmptyAttributes;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\FunctionDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Definition\FakeFunctionDefinition;
use CuyZ\Valinor\Type\Types\NativeStringType;
use DateTime;
use Error;
use PHPUnit\Framework\TestCase;
use stdClass;

use function uniqid;

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
            'foo/bar',
            stdClass::class,
            new Parameters(
                new ParameterDefinition(
                    'bar',
                    'foo::bar',
                    NativeStringType::get(),
                    false,
                    false,
                    'foo',
                    EmptyAttributes::get()
                )
            ),
            NativeStringType::get()
        );

        $code = $this->compiler->compile($function);
        $compiledFunction = $this->eval($code);

        self::assertInstanceOf(FunctionDefinition::class, $compiledFunction);
        self::assertSame('foo', $compiledFunction->name());
        self::assertSame('foo:42-1337', $compiledFunction->signature());
        self::assertSame('foo/bar', $compiledFunction->fileName());
        self::assertSame(stdClass::class, $compiledFunction->class());
        self::assertTrue($compiledFunction->parameters()->has('bar'));
        self::assertInstanceOf(NativeStringType::class, $compiledFunction->returnType());
    }

    public function test_modifying_function_definition_file_invalids_compiled_function_definition(): void
    {
        $fileName = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . ".php";

        file_put_contents($fileName, "<?php function _valinor_test_modifying_function_definition_file_invalids_compiled_function_definition() {}");

        include $fileName;

        $class = FakeFunctionDefinition::new($fileName);

        $validationCode = $this->compiler->compileValidation($class);
        $firstValidation = $this->eval($validationCode);

        unlink($fileName);
        touch($fileName, (new DateTime('+5 seconds'))->getTimestamp());

        $secondValidation = $this->eval($validationCode);

        unlink($fileName);

        self::assertTrue($firstValidation);
        self::assertFalse($secondValidation);
    }

    /**
     * @return FunctionDefinition|bool
     */
    private function eval(string $code)
    {
        try {
            return eval("return $code;");
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }
    }
}
