<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilledArguments;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;

use function is_array;

/** @internal */
final class InterfaceNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private ObjectImplementations $implementations;

    private ClassDefinitionRepository $classDefinitionRepository;

    private ObjectBuilderFactory $objectBuilderFactory;

    private bool $flexible;

    public function __construct(
        NodeBuilder $delegate,
        ObjectImplementations $implementations,
        ClassDefinitionRepository $classDefinitionRepository,
        ObjectBuilderFactory $objectBuilderFactory,
        bool $flexible
    ) {
        $this->delegate = $delegate;
        $this->implementations = $implementations;
        $this->classDefinitionRepository = $classDefinitionRepository;
        $this->objectBuilderFactory = $objectBuilderFactory;
        $this->flexible = $flexible;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();

        if (! $type instanceof InterfaceType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $interfaceName = $type->className();

        $function = $this->implementations->function($interfaceName);
        $arguments = Arguments::fromParameters($function->parameters());
        $arguments = FilledArguments::forInterface($arguments, $shell, $this->flexible);

        $children = $this->children($shell, $arguments, $rootBuilder);
        $values = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return Node::branch($shell, null, $children);
            }

            $values[] = $child->value();
        }

        try {
            $classType = $this->implementations->implementation($interfaceName, $values);
        } catch (ObjectImplementationCallbackError $exception) {
            throw ThrowableMessage::from($exception->original());
        }

        $shell = $shell->withType($classType);
        $shell = $this->removeKeysFromSource($shell, $arguments, $classType);

        return $rootBuilder->build($shell);
    }

    /**
     * @return Node[]
     */
    private function children(Shell $shell, FilledArguments $arguments, RootNodeBuilder $rootBuilder): array
    {
        $children = [];

        foreach ($arguments as $argument) {
            $name = $argument->name();
            $type = $argument->type();
            $attributes = $argument->attributes();

            $child = $shell->child($name, $type, $attributes);

            if ($arguments->has($name)) {
                $child = $child->withValue($arguments->get($name));
            }

            $children[] = $rootBuilder->build($child);
        }

        return $children;
    }

    private function removeKeysFromSource(Shell $shell, FilledArguments $arguments, ClassType $classType): Shell
    {
        $value = $shell->value();

        if (! is_array($value)) {
            return $shell;
        }

        $class = $this->classDefinitionRepository->for($classType);
        $builders = $this->objectBuilderFactory->for($class);

        foreach ($arguments as $argument) {
            $name = $argument->name();

            foreach ($builders as $builder) {
                if ($builder->describeArguments()->has($name)) {
                    continue 2;
                }
            }

            unset($value[$name]);
        }

        return $shell->withValue($value);
    }
}
