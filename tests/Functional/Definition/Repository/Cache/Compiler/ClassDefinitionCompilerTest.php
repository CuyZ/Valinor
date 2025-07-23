<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Functional\Definition\Repository\Cache\Compiler;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\Repository\Cache\Compiler\ClassDefinitionCompiler;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\Reflection\ReflectionClassDefinitionRepository;
use CuyZ\Valinor\Tests\Fake\Definition\FakeClassDefinition;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;
use CuyZ\Valinor\Type\Parser\Factory\LexingTypeParserFactory;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use Error;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function implode;

final class ClassDefinitionCompilerTest extends TestCase
{
    private ClassDefinitionCompiler $compiler;

    private ClassDefinitionRepository $classDefinitionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->compiler = new ClassDefinitionCompiler();
        $this->classDefinitionRepository = new ReflectionClassDefinitionRepository(
            new LexingTypeParserFactory(),
            [],
        );
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

                public static function methodWithDefaultObjectValue(StringableObject $object = new StringableObject('bar')): StringableObject
                {
                    return $object;
                }
            };

        $className = $object::class;
        $class = $this->classDefinitionRepository->for(new NativeClassType($className));

        $class = $this->eval($this->compiler->compile($class));

        self::assertInstanceOf(ClassDefinition::class, $class);

        self::assertSame($className, $class->name);
        self::assertSame($className, $class->type->className());
        self::assertFalse($class->isFinal);
        self::assertFalse($class->isAbstract);

        $properties = $class->properties;

        self::assertTrue($properties->has('property'));

        $property = $properties->get('property');

        self::assertSame('property', $property->name);
        self::assertSame($className . '::$property', $property->signature);
        self::assertSame(NativeStringType::get(), $property->type);
        self::assertTrue($property->hasDefaultValue);
        self::assertSame('Some property default value', $property->defaultValue);
        self::assertTrue($property->isPublic);

        $method = $class->methods->get('method');

        self::assertSame('method', $method->name);
        self::assertSame($className . '::method()', $method->signature);
        self::assertTrue($method->isStatic);
        self::assertTrue($method->isPublic);
        self::assertSame(NativeStringType::get(), $method->returnType);

        $parameter = $method->parameters->get('parameter');

        self::assertSame('parameter', $parameter->name);
        self::assertSame($className . '::method($parameter)', $parameter->signature);
        self::assertSame(NativeStringType::get(), $parameter->type);
        self::assertTrue($parameter->isOptional);
        self::assertFalse($parameter->isVariadic);
        self::assertSame('Some parameter default value', $parameter->defaultValue);

        $variadic = $method->parameters->get('variadic');

        self::assertTrue($variadic->isVariadic);

        $methodWithDefaultObjectValue = $class->methods->get('methodWithDefaultObjectValue');
        $parameterWithDefaultObjectValue = $methodWithDefaultObjectValue->parameters->get('object');

        self::assertInstanceOf(StringableObject::class, $parameterWithDefaultObjectValue->defaultValue);
    }

    public function test_final_class_is_compiled_correctly(): void
    {
        $class = FakeClassDefinition::fromReflection(new ReflectionClass(SomeFinalClass::class));

        $class = $this->eval($this->compiler->compile($class));

        self::assertInstanceOf(ClassDefinition::class, $class);
        self::assertTrue($class->isFinal);
    }

    public function test_abstract_class_is_compiled_correctly(): void
    {
        $class = FakeClassDefinition::fromReflection(new ReflectionClass(SomeAbstractClass::class));

        $class = $this->eval($this->compiler->compile($class));

        self::assertInstanceOf(ClassDefinition::class, $class);
        self::assertTrue($class->isAbstract);
    }

    private function eval(string $code): mixed
    {
        try {
            return eval("return $code;");
        } catch (Error $exception) {
            self::fail($exception->getMessage());
        }
    }
}

final class SomeFinalClass {}

abstract class SomeAbstractClass {}
