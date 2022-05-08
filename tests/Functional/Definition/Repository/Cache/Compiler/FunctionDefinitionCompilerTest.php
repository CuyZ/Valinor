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
use Error;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use stdClass;

use function time;
use function unlink;

final class FunctionDefinitionCompilerTest extends TestCase
{
    private vfsStreamDirectory $files;

    private FunctionDefinitionCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = vfsStream::setup();

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
        $file = (vfsStream::newFile('foo.php'))
            ->withContent('<?php function _valinor_test_modifying_function_definition_file_invalids_compiled_function_definition() {}')
            ->at($this->files);

        $class = FakeFunctionDefinition::new($file->url());
        $validationCode = $this->compiler->compileValidation($class);

        $firstValidation = $this->eval($validationCode);

        unlink($file->url());

        $file->lastModified(time() + 5)->at($this->files);

        $secondValidation = $this->eval($validationCode);

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
