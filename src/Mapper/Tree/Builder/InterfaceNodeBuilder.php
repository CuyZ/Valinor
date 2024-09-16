<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSource;
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
        private FunctionsContainer $constructors,
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

        if ($shell->enableFlexibleCasting() && $shell->value() === null) {
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

        $argumentsValues = ArgumentsValues::forInterface($arguments, $shell);

        if ($argumentsValues->hasInvalidValue()) {
            throw new InvalidSource($shell->value(), $arguments);
        }

        $children = $this->children($shell, $argumentsValues, $rootBuilder);

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

        $shell = $shell->withType($classType);
        $shell = $shell->withAllowedSuperfluousKeys($arguments->names());

        return $this->delegate->build($shell, $rootBuilder);
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

    /**
     * @return array<TreeNode>
     */
    private function children(Shell $shell, ArgumentsValues $arguments, RootNodeBuilder $rootBuilder): array
    {
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
