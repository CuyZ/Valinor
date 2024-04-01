<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilteredObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotInferFinalClass;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveObjectType;
use CuyZ\Valinor\Mapper\Tree\Exception\InterfaceHasBothConstructorAndInfer;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;

/** @internal */
final class InterfaceNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private ObjectImplementations $implementations,
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory,
        private FilteredObjectNodeBuilder $filteredObjectNodeBuilder,
        private FunctionsContainer $constructors,
        private bool $enableFlexibleCasting,
        private bool $allowSuperfluousKeys,
    ) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        if (! $type instanceof InterfaceType && ! $type instanceof NativeClassType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        if ($this->constructorRegisteredFor($type)) {
            if ($this->implementations->has($type->className())) {
                throw new InterfaceHasBothConstructorAndInfer($type->className());
            }

            return $this->delegate->build($shell, $rootBuilder);
        }

        if ($this->enableFlexibleCasting && $shell->value() === null) {
            $shell = $shell->withValue([]);
        }

        $className = $type->className();

        if (! $this->implementations->has($className)) {
            if ($type instanceof InterfaceType || $this->classDefinitionRepository->for($type)->isAbstract) {
                throw new CannotResolveObjectType($className);
            }

            return $this->delegate->build($shell, $rootBuilder);
        }

        $function = $this->implementations->function($className);
        $arguments = Arguments::fromParameters($function->parameters);

        if ($type instanceof NativeClassType && $this->classDefinitionRepository->for($type)->isFinal) {
            throw new CannotInferFinalClass($type, $function);
        }

        $children = $this->children($shell, $arguments, $rootBuilder);

        $values = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return TreeNode::branch($shell, null, $children);
            }

            $values[] = $child->value();
        }

        try {
            $classType = $this->implementations->implementation($className, $values);
        } catch (ObjectImplementationCallbackError $exception) {
            throw UserlandError::from($exception);
        }

        $class = $this->classDefinitionRepository->for($classType);
        $objectBuilder = FilteredObjectBuilder::from($shell->value(), ...$this->objectBuilderFactory->for($class));

        $shell = $this->transformSourceForClass($shell, $arguments, $objectBuilder->describeArguments());

        return $this->filteredObjectNodeBuilder->build($objectBuilder, $shell, $rootBuilder);
    }

    private function constructorRegisteredFor(Type $type): bool
    {
        foreach ($this->constructors as $constructor) {
            if ($type->matches($constructor->definition->returnType)) {
                return true;
            }
        }

        return false;
    }

    private function transformSourceForClass(Shell $shell, Arguments $interfaceArguments, Arguments $classArguments): Shell
    {
        $value = $shell->value();

        if (! is_array($value)) {
            return $shell;
        }

        foreach ($interfaceArguments as $argument) {
            $name = $argument->name();

            if (array_key_exists($name, $value) && ! $classArguments->has($name)) {
                unset($value[$name]);
            }
        }

        if (count($classArguments) === 1 && count($value) === 1) {
            $name = $classArguments->at(0)->name();

            if (array_key_exists($name, $value)) {
                $value = $value[$name];
            }
        }

        return $shell->withValue($value);
    }

    /**
     * @return array<TreeNode>
     */
    private function children(Shell $shell, Arguments $arguments, RootNodeBuilder $rootBuilder): array
    {
        $arguments = ArgumentsValues::forInterface($arguments, $shell->value(), $this->allowSuperfluousKeys);

        $children = [];

        foreach ($arguments as $argument) {
            $name = $argument->name();
            $type = $argument->type();
            $attributes = $argument->attributes();

            $child = $shell->child($name, $type, $attributes);

            if ($arguments->hasValue($name)) {
                $child = $child->withValue($arguments->getValue($name));
            }

            $children[] = $rootBuilder->build($child);
        }

        return $children;
    }
}
