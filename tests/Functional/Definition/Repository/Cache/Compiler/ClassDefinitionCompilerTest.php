<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fixture\Object\ObjectWithParameterDefaultObjectValue;
use CuyZ\Valinor\Type\Types\NativeStringType;
use Error;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function get_class;
use function implode;
use function time;
use function unlink;

final class ClassDefinitionCompilerTest extends TestCase
{
    private vfsStreamDirectory $files;

    private ClassDefinitionCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = vfsStream::setup();

        $this->compiler = new ClassDefinitionCompiler();
    }

    public function test_class_definition_is_compiled_correctly(): void
    {
        $object =
            new class () {
                public string $property = 'Some property default value';

                public static function method(string $parameter = 'Some parameter default value', string ...$variadic): string
                {
                    return $parameter . implode(' / ', $variadic);
                }
            };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $className = get_class($object);

        $class = $this->eval($this->compiler->compile($class));

        self::assertInstanceOf(ClassDefinition::class, $class);

        self::assertSame($className, $class->name());
        self::assertSame($className, $class->type()->className());

        $properties = $class->properties();

        self::assertTrue($properties->has('property'));

        $property = $properties->get('property');

        self::assertSame('property', $property->name());
        self::assertSame('Signature::property', $property->signature());
        self::assertSame(NativeStringType::get(), $property->type());
        self::assertTrue($property->hasDefaultValue());
        self::assertSame('Some property default value', $property->defaultValue());
        self::assertTrue($property->isPublic());

        $method = $class->methods()->get('method');

        self::assertSame('method', $method->name());
        self::assertSame('Signature::method', $method->signature());
        self::assertTrue($method->isStatic());
        self::assertTrue($method->isPublic());
        self::assertSame(NativeStringType::get(), $method->returnType());

        $parameter = $method->parameters()->get('parameter');

        self::assertSame('parameter', $parameter->name());
        self::assertSame('Signature::parameter', $parameter->signature());
        self::assertSame(NativeStringType::get(), $parameter->type());
        self::assertTrue($parameter->isOptional());
        self::assertFalse($parameter->isVariadic());
        self::assertSame('Some parameter default value', $parameter->defaultValue());

        $variadic = $method->parameters()->get('variadic');

        self::assertTrue($variadic->isVariadic());
    }

    /**
     * @PHP8.1 move to test above
     *
     * @requires PHP >= 8.1
     */
    public function test_parameter_with_object_default_value_is_compiled_correctly(): void
    {
        $class = FakeClassDefinition::fromReflection(new ReflectionClass(ObjectWithParameterDefaultObjectValue::class));

        $class = $this->eval($this->compiler->compile($class));

        self::assertInstanceOf(ClassDefinition::class, $class);
        self::assertSame(ObjectWithParameterDefaultObjectValue::class, $class->name());
    }

    public function test_modifying_class_definition_file_invalids_compiled_class_definition(): void
    {
        /** @var class-string $className */
        $className = 'SomeClassDefinitionForTest';

        $file = (vfsStream::newFile("$className.php"))
            ->withContent("<?php final class $className {}")
            ->at($this->files);

        include $file->url();

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($className));

        $validationCode = $this->compiler->compileValidation($class);
        $firstValidation = $this->eval($validationCode);

        unlink($file->url());

        $file->lastModified(time() + 5)->at($this->files);

        $secondValidation = $this->eval($validationCode);

        self::assertTrue($firstValidation);
        self::assertFalse($secondValidation);
    }

    public function test_compile_validation_for_internal_class_returns_true(): void
    {
        $code = $this->compiler->compileValidation(FakeClassDefinition::new());

        self::assertSame('true', $code);
    }

    /**
     * @return mixed
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
