<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringArgumentsMapping;
use CuyZ\Valinor\Mapper\Tree\Builder\RootNodeBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\UnresolvableShellType;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;

/** @internal */
final class TypeArgumentsMapper implements ArgumentsMapper
{
    private FunctionDefinitionRepository $functionDefinitionRepository;

    private RootNodeBuilder $nodeBuilder;

    public function __construct(FunctionDefinitionRepository $functionDefinitionRepository, RootNodeBuilder $nodeBuilder)
    {
        $this->functionDefinitionRepository = $functionDefinitionRepository;
        $this->nodeBuilder = $nodeBuilder;
    }

    /** @pure */
    public function mapArguments(callable $callable, mixed $source): array
    {
        $function = $this->functionDefinitionRepository->for($callable);

        $elements = array_map(
            fn (ParameterDefinition $parameter) => new ShapedArrayElement(
                new StringValueType($parameter->name),
                $parameter->type,
                $parameter->isOptional
            ),
            iterator_to_array($function->parameters)
        );

        $type = new ShapedArrayType(...$elements);
        $shell = Shell::root($type, $source);

        try {
            $node = $this->nodeBuilder->build($shell);
        } catch (UnresolvableShellType $exception) {
            throw new TypeErrorDuringArgumentsMapping($function, $exception);
        }

        if (! $node->isValid()) {
            throw new ArgumentsMapperError($function, $node->node());
        }

        /** @var array<string, mixed> */
        return $node->value();
    }
}
