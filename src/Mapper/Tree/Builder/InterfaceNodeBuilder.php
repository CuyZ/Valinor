<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSourceForInterface;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidInterfaceResolverReturnType;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidTypeResolvedForInterface;
use CuyZ\Valinor\Mapper\Tree\Exception\ResolvedTypeForInterfaceIsNotAccepted;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveTypeFromInterface;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use Exception;

/** @internal */
final class InterfaceNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private FunctionsContainer $functions;

    private TypeParser $typeParser;

    public function __construct(NodeBuilder $delegate, FunctionsContainer $functions, TypeParser $typeParser)
    {
        $this->delegate = $delegate;
        $this->functions = $functions;
        $this->typeParser = $typeParser;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();

        if (! $type instanceof InterfaceType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $interfaceName = $type->className();

        if (! $this->functions->has($interfaceName)) {
            throw new CannotResolveTypeFromInterface($interfaceName);
        }

        $function = $this->functions->get($interfaceName);
        $callback = $this->functions->callback($function);

        $children = $this->children($shell, $function, $rootBuilder);
        $arguments = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return Node::branch($shell, null, $children);
            }

            $arguments[] = $child->value();
        }

        try {
            $signature = $callback(...$arguments);
        } catch (Exception $exception) {
            throw ThrowableMessage::from($exception);
        }

        if (! is_string($signature)) {
            throw new InvalidInterfaceResolverReturnType($interfaceName, $signature);
        }

        $classType = $this->typeParser->parse($signature);

        if (! $classType instanceof ClassType) {
            throw new InvalidTypeResolvedForInterface($interfaceName, $classType);
        }

        if (! $type->matches($classType)) {
            throw new ResolvedTypeForInterfaceIsNotAccepted($interfaceName, $classType);
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

        if (is_iterable($source) && ! is_array($source)) {
            $source = iterator_to_array($source);
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
