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

use function array_filter;
use function array_key_exists;
use function array_values;
use function count;
use function in_array;
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
        $builders = [];

        foreach ($this->filteredConstructors() as $constructor) {
            if (! $this->constructorMatches($constructor, $class->type)) {
                continue;
            }

            if ($constructor->definition->class && $constructor->definition->isStatic && ! $constructor->definition->isClosure) {
                $scopedClass = is_a($class->name, $constructor->definition->class, true) ? $class->name : $constructor->definition->class;

                $builders[$constructor->definition->signature] = new MethodObjectBuilder($scopedClass, $constructor->definition->name, $constructor->definition->parameters);
            } else {
                $builders[$constructor->definition->signature] = new FunctionObjectBuilder($constructor, $class->type);
            }
        }

        foreach ($class->methods as $method) {
            if (! $method->isStatic) {
                continue;
            }

            if (! $method->attributes->has(Constructor::class)) {
                continue;
            }

            if (! $method->returnType instanceof ClassType) {
                throw new InvalidConstructorMethodWithAttributeReturnType($class->name, $method);
            }

            if (! is_a($class->name, $method->returnType->className(), true)) {
                throw new InvalidConstructorMethodWithAttributeReturnType($class->name, $method);
            }

            if (! $class->type->matches($method->returnType)) {
                continue;
            }

            $builders[$method->signature] = new MethodObjectBuilder($class->name, $method->name, $method->parameters);
        }

        if ($class->type instanceof EnumType) {
            $buildersWithOneArguments = array_filter($builders, fn (ObjectBuilder $builder) => $builder->describeArguments()->count() === 1);

            if (count($buildersWithOneArguments) === 0) {
                $builders[] = new NativeEnumObjectBuilder($class->type);
            }
        } elseif ($class->methods->hasConstructor()
            && $class->methods->constructor()->isPublic
            && (
                count($builders) === 0
                || $class->methods->constructor()->attributes->has(Constructor::class)
                || array_key_exists($class->name, $this->nativeConstructors)
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

        if ($parameterType->subTypes() === []) {
            return true;
        }

        foreach ($parameterType->subTypes() as $subType) {
            if ($classType->matches($subType)) {
                return true;
            }
        }

        return false;
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
