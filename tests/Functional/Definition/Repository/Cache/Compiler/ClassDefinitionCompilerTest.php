<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Type\Types\NativeStringType;
use DateTime;
use Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function file_put_contents;
use function get_class;
use function sys_get_temp_dir;
use function touch;
use function unlink;

final class ClassDefinitionCompilerTest extends TestCase
{
    private ClassDefinitionCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new ClassDefinitionCompiler();
    }

    public function test_class_definition_is_compiled_correctly(): void
    {
        $object =
            new class () {
                public string $property = 'Some property default value';

                public static function method(string $parameter = 'Some parameter default value'): string
                {
                    return $parameter;
                }
            };

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($object));
        $className = get_class($object);

        /** @var ClassDefinition $class */
        $class = $this->eval($this->compiler->compile($class));

        self::assertInstanceOf(ClassDefinition::class, $class);

        self::assertSame($className, $class->name());
        self::assertSame("Signature::$className", $class->signature());

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
        self::assertSame('Some parameter default value', $parameter->defaultValue());
    }

    public function test_modifying_class_definition_file_invalids_compiled_class_definition(): void
    {
        /** @var class-string $className */
        $className = 'SomeClassDefinitionForTest';

        $filename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "$className.php";

        file_put_contents($filename, "<?php final class $className {}");

        include $filename;

        $class = FakeClassDefinition::fromReflection(new ReflectionClass($className));

        $validationCode = $this->compiler->compileValidation($class);
        $firstValidation = $this->eval($validationCode);

        unlink($filename);
        touch($filename, (new DateTime('+5 seconds'))->getTimestamp());

        $secondValidation = $this->eval($validationCode);

        unlink($filename);

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
