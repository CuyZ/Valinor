<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeDuringValueConversion;
use CuyZ\Valinor\Mapper\Tree\Exception\ValueConverterHasNoArgument;
use CuyZ\Valinor\Mapper\Tree\Shell;

use function array_shift;
use function is_float;
use function is_nan;

/** @internal */
final class ValueConverterNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private FunctionsContainer $functions,
    ) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        try {
            $result = $this->unstack($this->functions->toArray(), $shell, $rootBuilder);

            $shell = $shell->withValue($result);

            return $this->delegate->build($shell, $rootBuilder);
        } catch (InvalidNodeDuringValueConversion $exception) {
            return $exception->node;
        }
    }

    /**
     * @param array<FunctionObject> $stack
     */
    private function unstack(array $stack, Shell $shell, RootNodeBuilder $rootBuilder): mixed
    {
        while ($current = array_shift($stack)) {
            if ($current->definition->parameters->count() === 0) {
                throw new ValueConverterHasNoArgument($current->definition);
            }

            if (! $shell->type()->matches($current->definition->returnType)) {
                continue;
            }

            if (! $current->definition->parameters->at(0)->type->accepts($shell->value())) {
                continue;
            }

            $arguments = [$shell->value()];

            if ($current->definition->parameters->count() >= 2) {
                $arguments[] = function (mixed $value = NAN) use ($stack, $shell, $rootBuilder) {
                    if (! is_float($value) || ! is_nan($value)) {
                        $shell = $shell->withValue($value);
                    }

                    return $this->unstack($stack, $shell, $rootBuilder);
                };
            }

            return ($current->callback)(...$arguments);
        }

        $node = $this->delegate->build($shell, $rootBuilder);

        if (! $node->isValid()) {
            throw new InvalidNodeDuringValueConversion($node);
        }

        return $node->value();
    }
}
