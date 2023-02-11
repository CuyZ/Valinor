<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\DynamicConstructor;
use CuyZ\Valinor\Mapper\Object\Exception\CannotInstantiateObject;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorClassTypeParameter;
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

use function array_key_exists;
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
        private FunctionsContainer $constructors
    ) {
    }

    public function for(ClassDefinition $class): array
    {
        $builders = $this->builders($class);

        if (count($builders) === 0) {
            if ($class->methods()->hasConstructor()) {
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
        $className = $class->name();
        $classType = $class->type();
        $methods = $class->methods();

        $builders = [];

        foreach ($this->filteredConstructors() as $constructor) {
            if (! $this->constructorMatches($constructor, $classType)) {
                continue;
            }

            $definition = $constructor->definition();
            $functionClass = $definition->class();

            if ($functionClass && $definition->isStatic() && ! $definition->isClosure()) {
                $scopedClass = is_a($className, $functionClass, true) ? $className : $functionClass;

                $builders[] = new MethodObjectBuilder($scopedClass, $definition->name(), $definition->parameters());
            } else {
                $builders[] = new FunctionObjectBuilder($constructor, $classType);
            }
        }

        if (! array_key_exists($className, $this->nativeConstructors) && count($builders) > 0) {
            return $builders;
        }

        if ($classType instanceof EnumType) {
            $builders[] = new NativeEnumObjectBuilder($classType);
        } elseif ($methods->hasConstructor() && $methods->constructor()->isPublic()) {
            $builders[] = new NativeConstructorObjectBuilder($class);
        }

        return $builders;
    }

    private function constructorMatches(FunctionObject $function, ClassType $classType): bool
    {
        $definition = $function->definition();
        $parameters = $definition->parameters();
        $returnType = $definition->returnType();

        if (! $classType->matches($returnType)) {
            return false;
        }

        if (! $definition->attributes()->has(DynamicConstructor::class)) {
            return true;
        }

        if (count($parameters) === 0) {
            throw new MissingConstructorClassTypeParameter($definition);
        }

        $parameterType = $parameters->at(0)->type();

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
                $function = $constructor->definition();

                if (enum_exists($function->class() ?? '') && in_array($function->name(), ['from', 'tryFrom'], true)) {
                    continue;
                }

                if (! $function->returnType() instanceof ObjectType) {
                    throw new InvalidConstructorReturnType($function);
                }

                $this->filteredConstructors[] = $constructor;
            }
        }

        return $this->filteredConstructors;
    }
}
