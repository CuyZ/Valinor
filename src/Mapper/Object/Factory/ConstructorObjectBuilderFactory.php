<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\Constructor;
use CuyZ\Valinor\Mapper\Object\DynamicConstructor;
use CuyZ\Valinor\Mapper\Object\Exception\CannotInstantiateObject;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorClassTypeParameter;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodWithAttributeReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\MissingConstructorClassTypeParameter;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\NativeConstructorObjectBuilder;
use CuyZ\Valinor\Mapper\Object\NativeEnumObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Utility\Reflection\Reflection;

use function array_key_exists;
use function array_values;
use function count;
use function is_a;

/** @internal */
final class ConstructorObjectBuilderFactory implements ObjectBuilderFactory
{
    /** @var list<FunctionObject> */
    private array $filteredConstructors;

    public function __construct(
        private ObjectBuilderFactory $delegate,
        /** @var array<class-string, null> */
        private array $nativeConstructors,
        private FunctionsContainer $constructors,
    ) {}

    public function for(ClassDefinition $class): array
    {
        $builders = $this->builders($class);

        if (count($builders) === 0) {
            if ($class->methods->hasConstructor()) {
                throw new CannotInstantiateObject($class);
            }

            return $this->delegate->for($class);
        }

        return $builders;
    }

    /**
     * @return list<ObjectBuilder>
     */
    private function builders(ClassDefinition $class): array
    {
        $className = $class->name;
        $classType = $class->type;
        $methods = $class->methods;

        $builders = [];

        foreach ($this->filteredConstructors() as $constructor) {
            if (! $this->constructorMatches($constructor, $classType)) {
                continue;
            }

            $definition = $constructor->definition;
            $functionClass = $definition->class;

            if ($functionClass && $definition->isStatic && ! $definition->isClosure) {
                $scopedClass = is_a($className, $functionClass, true) ? $className : $functionClass;

                $builders[$definition->signature] = new MethodObjectBuilder($scopedClass, $definition->name, $definition->parameters);
            } else {
                $builders[$definition->signature] = new FunctionObjectBuilder($constructor, $classType);
            }
        }

        foreach ($methods as $method) {
            if (! $method->isStatic) {
                continue;
            }

            if (! $method->attributes->has(Constructor::class)) {
                continue;
            }

            if (! $method->returnType instanceof ClassType) {
                throw new InvalidConstructorMethodWithAttributeReturnType($className, $method);
            }

            if (! is_a($className, $method->returnType->className(), true)) {
                throw new InvalidConstructorMethodWithAttributeReturnType($className, $method);
            }

            if (! $class->type->matches($method->returnType)) {
                continue;
            }

            $builders[$method->signature] = new MethodObjectBuilder($className, $method->name, $method->parameters);
        }

        if ($classType instanceof EnumType) {
            $buildersWithOneArguments = array_filter($builders, fn (ObjectBuilder $builder) => $builder->describeArguments()->count() === 1);

            if (count($buildersWithOneArguments) === 0) {
                $builders[] = new NativeEnumObjectBuilder($classType);
            }
        } elseif ($methods->hasConstructor()
            && $methods->constructor()->isPublic
            && (
                count($builders) === 0
                || $methods->constructor()->attributes->has(Constructor::class)
                || array_key_exists($className, $this->nativeConstructors)
            )
        ) {
            $builders[] = new NativeConstructorObjectBuilder($class);
        }

        return array_values($builders);
    }

    private function constructorMatches(FunctionObject $function, ObjectType $classType): bool
    {
        $definition = $function->definition;

        if (! $classType->matches($definition->returnType)) {
            return false;
        }

        if (! $definition->attributes->has(DynamicConstructor::class)) {
            return true;
        }

        if (count($definition->parameters) === 0) {
            throw new MissingConstructorClassTypeParameter($definition);
        }

        $parameterType = $definition->parameters->at(0)->type;

        if ($parameterType instanceof NativeStringType) {
            $parameterType = ClassStringType::get();
        }

        if (! $parameterType instanceof ClassStringType) {
            throw new InvalidConstructorClassTypeParameter($definition, $parameterType);
        }

        $subType = $parameterType->subType();

        if ($subType) {
            return $classType->matches($subType);
        }

        return true;
    }

    /**
     * @return list<FunctionObject>
     */
    private function filteredConstructors(): array
    {
        if (! isset($this->filteredConstructors)) {
            $this->filteredConstructors = [];

            foreach ($this->constructors as $constructor) {
                $function = $constructor->definition;

                if ($function->class
                    && Reflection::enumExists($function->class)
                    && in_array($function->name, ['from', 'tryFrom'], true)
                ) {
                    continue;
                }

                if (! $function->returnType instanceof ObjectType) {
                    throw new InvalidConstructorReturnType($function);
                }

                $this->filteredConstructors[] = $constructor;
            }
        }

        return $this->filteredConstructors;
    }
}
