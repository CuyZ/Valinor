<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Tree\Exception\ConverterHasInvalidReturnType;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeDuringValueConversion;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Types\Generics;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use Exception;
use Throwable;

use function array_map;
use function array_shift;
use function func_get_arg;
use function func_num_args;

/** @internal */
final class ValueConverterNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private ConverterContainer $converterContainer,
        private ClassDefinitionRepository $classDefinitionRepository,
        private FunctionDefinitionRepository $functionDefinitionRepository,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function build(Shell $shell): Node
    {
        $attributes = $shell->attributes;

        if ($shell->type instanceof ObjectType) {
            $class = $this->classDefinitionRepository->for($shell->type);

            $attributes = $attributes->merge($class->attributes);
        }

        // @infection-ignore-all (This is a performance optimization, we don't test this)
        if ($attributes->count() === 0 && $this->converterContainer->converters() === []) {
            return $this->delegate->build($shell);
        }

        $converterAttributes = $attributes->filter(ConverterContainer::filterConverterAttributes(...));

        $stack = array_map(
            // @phpstan-ignore method.notFound (we know the `map` method exists)
            static fn (AttributeDefinition $attribute) => $attribute->instantiate()->map(...),
            $converterAttributes->toArray(),
        );

        if ($shell->shouldApplyConverters) {
            $stack = [...$stack, ...$this->converterContainer->converters()];
        }

        if ($stack === []) {
            // @infection-ignore-all (This is a performance optimization, we don't test this)
            return $this->delegate->build($shell);
        }

        try {
            $result = $this->unstack($stack, $shell);

            if (! $shell->type->accepts($result)) {
                return $shell->withValue($result)->error(InvalidNodeValue::from($shell->type));
            }

            return $shell->node($result);
        } catch (InvalidNodeDuringValueConversion $exception) {
            return $exception->node;
        }
    }

    /**
     * @param array<callable> $stack
     */
    private function unstack(array $stack, Shell $shell): mixed
    {
        while ($current = array_shift($stack)) {
            $converter = $this->functionDefinitionRepository->for($current);

            if ($converter->returnType instanceof UnresolvableType) {
                throw new ConverterHasInvalidReturnType($converter);
            }

            $generics = $converter->returnType->inferGenericsFrom($shell->type, new Generics());
            $converter = $converter->assignGenerics($generics);

            $firstParameterType = $converter->parameters->at(0)->type;
            $returnType = $converter->returnType;

            if ($firstParameterType instanceof UnresolvableType || $returnType instanceof UnresolvableType) {
                continue;
            }

            if (! $converter->returnType->matches($shell->type)) {
                continue;
            }

            if (! $firstParameterType->accepts($shell->value())) {
                continue;
            }

            $arguments = [$shell->value()];

            if ($converter->parameters->count() > 1) {
                $arguments[] = function () use ($stack, $shell) {
                    if (func_num_args() > 0) {
                        $shell = $shell->withValue(func_get_arg(0));
                    }

                    return $this->unstack($stack, $shell);
                };
            }

            try {
                return $current(...$arguments);
            } catch (Exception $exception) {
                if (! $exception instanceof Message) {
                    $exception = ($this->exceptionFilter)($exception);
                }

                $error = $shell->error($exception);

                throw new InvalidNodeDuringValueConversion($error);
            }
        }

        $node = $this->delegate->build($shell);

        if (! $node->isValid()) {
            throw new InvalidNodeDuringValueConversion($node);
        }

        return $node->value();
    }
}
