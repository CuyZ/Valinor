<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
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
use DateTimeImmutable;
use DateTimeInterface;
use Exception;

/** @internal */
final class InterfaceNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private TypeParser $typeParser;

    private FunctionDefinitionRepository $functionDefinitionRepository;

    /** @var array<interface-string, callable> */
    private array $callbacks;

    /** @var array<interface-string, FunctionDefinition> */
    private array $functions;

    /**
     * @param array<interface-string, callable> $interfaceMapping
     */
    public function __construct(
        NodeBuilder $delegate,
        FunctionDefinitionRepository $functionDefinitionRepository,
        TypeParser $typeParser,
        array $interfaceMapping
    ) {
        $this->delegate = $delegate;
        $this->functionDefinitionRepository = $functionDefinitionRepository;
        $this->typeParser = $typeParser;

        $this->callbacks = $interfaceMapping;
        $this->callbacks[DateTimeInterface::class] ??= static fn () => DateTimeImmutable::class;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();

        if (! $type instanceof InterfaceType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $interfaceName = $type->className();

        if (! isset($this->callbacks[$interfaceName])) {
            throw new CannotResolveTypeFromInterface($interfaceName);
        }

        /** @infection-ignore-all */
        $this->functions[$interfaceName] ??= $this->functionDefinitionRepository->for($this->callbacks[$interfaceName]);

        $children = $this->children($shell, $this->functions[$interfaceName], $rootBuilder);
        $arguments = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return Node::branch($shell, null, $children);
            }

            $arguments[] = $child->value();
        }

        try {
            $signature = ($this->callbacks[$interfaceName])(...$arguments);
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
