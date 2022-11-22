<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Type\Types\ShapedArrayElement;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\StringValueType;

use function array_values;

/** @internal */
final class TypeArgumentsMapper implements ArgumentsMapper
{
    private TreeMapper $delegate;

    private FunctionDefinitionRepository $functionDefinitionRepository;

    public function __construct(TreeMapper $delegate, FunctionDefinitionRepository $functionDefinitionRepository)
    {
        $this->delegate = $delegate;
        $this->functionDefinitionRepository = $functionDefinitionRepository;
    }

    /** @pure */
    public function mapArguments(callable $callable, $source): array
    {
        $function = $this->functionDefinitionRepository->for($callable);
        $parameters = $function->parameters();

        $signature = $this->signature($parameters);

        try {
            $result = $this->delegate->map($signature, $source);
        } catch (MappingError $error) {
            throw new ArgumentsMapperError($function, $error->node());
        }

        if (count($parameters) === 1) {
            $result = [$parameters->at(0)->name() => $result];
        }

        return $result;
    }

    private function signature(Parameters $parameters): string
    {
        if (count($parameters) === 1) {
            return $parameters->at(0)->type()->toString();
        }

        $elements = array_map(
            fn (ParameterDefinition $parameter) => new ShapedArrayElement(
                new StringValueType($parameter->name()),
                $parameter->type(),
                $parameter->isOptional()
            ),
            iterator_to_array($parameters)
        );

        // PHP8.0 Remove `array_values`
        return (new ShapedArrayType(...array_values($elements)))->toString();
    }
}
