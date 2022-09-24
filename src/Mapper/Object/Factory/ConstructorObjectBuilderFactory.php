<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\BackwardCompatibilityDateTimeConstructor;
use CuyZ\Valinor\Mapper\Object\DateTimeFormatConstructor;
use CuyZ\Valinor\Mapper\Object\DynamicConstructor;
use CuyZ\Valinor\Mapper\Object\Exception\CannotInstantiateObject;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorClassTypeParameter;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\MissingConstructorClassTypeParameter;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\NativeConstructorObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeStringType;

use function array_key_exists;
use function count;

/** @internal */
final class ConstructorObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    /** @var array<class-string, null> */
    public array $nativeConstructors = [];

    private FunctionsContainer $constructors;

    /**
     * @param array<class-string, null> $nativeConstructors
     */
    public function __construct(
        ObjectBuilderFactory $delegate,
        array $nativeConstructors,
        FunctionsContainer $constructors
    ) {
        $this->delegate = $delegate;
        $this->nativeConstructors = $nativeConstructors;
        $this->constructors = $constructors;
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

        foreach ($this->constructors as $function) {
            if (! $this->constructorMatches($function, $classType)) {
                continue;
            }

            $definition = $function->definition();
            $functionClass = $definition->class();

            if ($functionClass && $definition->isStatic() && ! $definition->isClosure()) {
                $builders[] = new MethodObjectBuilder($className, $definition->name(), $definition->parameters());
            } else {
                $builders[] = new FunctionObjectBuilder($function, $classType);
            }
        }

        if (! array_key_exists($className, $this->nativeConstructors) && count($builders) > 0) {
            return $builders;
        }

        if ($methods->hasConstructor() && $methods->constructor()->isPublic()) {
            $builders[] = new NativeConstructorObjectBuilder($class);
        }

        return $builders;
    }

    private function constructorMatches(FunctionObject $function, ClassType $classType): bool
    {
        $definition = $function->definition();
        $parameters = $definition->parameters();
        $returnType = $definition->returnType();

        if (! $returnType instanceof ClassType && ! $returnType instanceof InterfaceType) {
            throw new InvalidConstructorReturnType($definition);
        }

        if (! $classType->matches($returnType)) {
            return false;
        }

        if (! $definition->attributes()->has(DynamicConstructor::class)
            // @PHP8.0 remove
            && $definition->class() !== DateTimeFormatConstructor::class
            && $definition->class() !== BackwardCompatibilityDateTimeConstructor::class
        ) {
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
}
