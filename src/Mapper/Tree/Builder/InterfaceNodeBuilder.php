<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSourceForInterface;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\InterfaceType;

use function count;

/** @internal */
final class InterfaceNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private ObjectImplementations $implementations;

    public function __construct(NodeBuilder $delegate, ObjectImplementations $implementations)
    {
        $this->delegate = $delegate;
        $this->implementations = $implementations;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();

        if (! $type instanceof InterfaceType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $interfaceName = $type->className();

        $function = $this->implementations->function($interfaceName);
        $children = $this->children($shell, $function, $rootBuilder);
        $arguments = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return Node::branch($shell, null, $children);
            }

            $arguments[] = $child->value();
        }

        try {
            $classType = $this->implementations->implementation($interfaceName, $arguments);
        } catch (ObjectImplementationCallbackError $exception) {
            throw ThrowableMessage::from($exception->original());
        }

        return $rootBuilder->build($shell->withType($classType));
    }

    /**
     * @return Node[]
     */
    private function children(Shell $shell, FunctionDefinition $definition, RootNodeBuilder $rootBuilder): array
    {
        $parameters = $definition->parameters();
        $source = $this->transformSource($shell->value(), $parameters);

        $children = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->name();
            $type = $parameter->type();
            $attributes = $parameter->attributes();
            $value = array_key_exists($name, $source) ? $source[$name] : $parameter->defaultValue();

            $children[] = $rootBuilder->build($shell->child($name, $type, $value, $attributes));
        }

        return $children;
    }

    /**
     * @param mixed $source
     * @return mixed[]
     */
    private function transformSource($source, Parameters $parameters): array
    {
        if ($source === null || count($parameters) === 0) {
            return [];
        }

        if (count($parameters) === 1) {
            $name = $parameters->at(0)->name();

            if (! is_array($source) || ! array_key_exists($name, $source)) {
                $source = [$name => $source];
            }
        }

        if (! is_array($source)) {
            throw new InvalidSourceForInterface($source);
        }

        return $source;
    }
}
