<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Exception\MappingLogicalException;
use CuyZ\Valinor\Mapper\Exception\TypeErrorDuringArgumentsMapping;
use CuyZ\Valinor\Mapper\Tree\RootNodeBuilder;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;

use function array_map;
use function count;

/** @internal */
final class TypeArgumentsMapper implements ArgumentsMapper
{
    public function __construct(
        private FunctionDefinitionRepository $functionDefinitionRepository,
        private RootNodeBuilder $nodeBuilder,
    ) {}

    /** @pure */
    public function mapArguments(callable $callable, mixed $source): array
    {
        $function = $this->functionDefinitionRepository->for($callable);

        $elements = array_map(
            fn (ParameterDefinition $parameter) => new ShapedArrayElement(
                new StringValueType($parameter->name),
                $parameter->type,
                $parameter->isOptional,
                $parameter->attributes,
            ),
            $function->parameters->toArray(),
        );

        $type = new ShapedArrayType($elements);

        try {
            $node = $this->nodeBuilder->build($source, $type, $function->attributes);
        } catch (MappingLogicalException $exception) {
            throw new TypeErrorDuringArgumentsMapping($function, $exception);
        }

        if ($node->isValid()) {
            /** @var array<string, mixed> */
            return $node->value();
        }

        // Transforms the source value if there is only one object argument, to
        // ensure the source can contain flattened values.
        if (count($elements) === 1 && $function->parameters->at(0)->type instanceof ObjectType) {
            $node = $this->nodeBuilder->build($source, $function->parameters->at(0)->type, $function->attributes);

            if ($node->isValid()) {
                /** @var array<string, mixed> */
                return [$function->parameters->at(0)->name => $node->value()];
            }
        }

        throw new ArgumentsMapperError($source, $type->toString(), $function->signature, $node->messages());
    }
}
