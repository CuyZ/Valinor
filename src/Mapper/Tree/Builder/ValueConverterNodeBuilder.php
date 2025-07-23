<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeDuringValueConversion;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use Exception;
use Throwable;

use function array_map;
use function array_shift;
use function is_float;
use function is_nan;

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

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $attributes = $shell->attributes();

        if ($type instanceof ObjectType) {
            $class = $this->classDefinitionRepository->for($type);

            $attributes = $attributes->merge($class->attributes);
        }

        // @infection-ignore-all (This is a performance optimization, we don't test this)
        if ($attributes->count() === 0 && $this->converterContainer->converters() === []) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $converterAttributes = $attributes->filter(ConverterContainer::filterConverterAttributes(...));

        $stack = [
            ...array_map(
                // @phpstan-ignore method.notFound (we know the `map` method exists)
                static fn (AttributeDefinition $attribute) => $attribute->instantiate()->map(...),
                $converterAttributes->toArray(),
            ),
            ...$this->converterContainer->converters(),
        ];

        if ($stack === []) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        try {
            $result = $this->unstack($stack, $shell, $rootBuilder);

            $shell = $shell->withValue($result);

            return $this->delegate->build($shell, $rootBuilder);
        } catch (InvalidNodeDuringValueConversion $exception) {
            return $exception->node;
        }
    }

    /**
     * @param array<callable> $stack
     */
    private function unstack(array $stack, Shell $shell, RootNodeBuilder $rootBuilder): mixed
    {
        while ($current = array_shift($stack)) {
            $converter = $this->functionDefinitionRepository->for($current);

            if (! $shell->type()->matches($converter->returnType)) {
                continue;
            }

            if (! $converter->parameters->at(0)->type->accepts($shell->value())) {
                continue;
            }

            $arguments = [$shell->value()];

            if ($converter->parameters->count() > 1) {
                $arguments[] = function (mixed $value = NAN) use ($stack, $shell, $rootBuilder) {
                    if (! is_float($value) || ! is_nan($value)) {
                        $shell = $shell->withValue($value);
                    }

                    return $this->unstack($stack, $shell, $rootBuilder);
                };
            }

            try {
                return $current(...$arguments);
            } catch (Exception $exception) {
                if (! $exception instanceof Message) {
                    $exception = ($this->exceptionFilter)($exception);
                }

                $error = Node::error($shell, $exception);

                throw new InvalidNodeDuringValueConversion($error);
            }
        }

        $node = $this->delegate->build($shell, $rootBuilder);

        if (! $node->isValid()) {
            throw new InvalidNodeDuringValueConversion($node);
        }

        return $node->value();
    }
}
