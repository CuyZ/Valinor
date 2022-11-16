<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilteredObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\InterfaceType;

/** @internal */
final class InterfaceNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private ObjectImplementations $implementations;

    private ClassDefinitionRepository $classDefinitionRepository;

    private ObjectBuilderFactory $objectBuilderFactory;

    private ClassNodeBuilder $classNodeBuilder;

    private bool $enableFlexibleCasting;

    public function __construct(
        NodeBuilder $delegate,
        ObjectImplementations $implementations,
        ClassDefinitionRepository $classDefinitionRepository,
        ObjectBuilderFactory $objectBuilderFactory,
        ClassNodeBuilder $classNodeBuilder,
        bool $enableFlexibleCasting
    ) {
        $this->delegate = $delegate;
        $this->implementations = $implementations;
        $this->classDefinitionRepository = $classDefinitionRepository;
        $this->objectBuilderFactory = $objectBuilderFactory;
        $this->classNodeBuilder = $classNodeBuilder;
        $this->enableFlexibleCasting = $enableFlexibleCasting;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        if (! $type instanceof InterfaceType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        if ($this->enableFlexibleCasting && $shell->value() === null) {
            $shell = $shell->withValue([]);
        }

        $interfaceName = $type->className();

        $function = $this->implementations->function($interfaceName);
        $arguments = Arguments::fromParameters($function->parameters());

        $children = $this->children($shell, $arguments, $rootBuilder);

        $values = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return TreeNode::branch($shell, null, $children);
            }

            $values[] = $child->value();
        }

        try {
            $classType = $this->implementations->implementation($interfaceName, $values);
        } catch (ObjectImplementationCallbackError $exception) {
            throw UserlandError::from($exception->original());
        }

        $class = $this->classDefinitionRepository->for($classType);
        $objectBuilder = new FilteredObjectBuilder($shell->value(), ...$this->objectBuilderFactory->for($class));

        $shell = $this->transformSourceForClass($shell, $arguments, $objectBuilder->describeArguments());

        return $this->classNodeBuilder->build($objectBuilder, $shell, $rootBuilder);
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
        $arguments = ArgumentsValues::forInterface($arguments, $shell->value());

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
