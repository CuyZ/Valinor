<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class ValueAlteringNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private FunctionsContainer $functions
    ) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $node = $this->delegate->build($shell, $rootBuilder);

        if (! $node->isValid()) {
            return $node;
        }

        $value = $node->value();

        foreach ($this->functions as $function) {
            $parameters = $function->definition->parameters;

            if (count($parameters) === 0) {
                continue;
            }

            $firstParameterType = $parameters->at(0)->type;

            if (! $firstParameterType->accepts($value)) {
                continue;
            }

            $value = ($function->callback)($value);
            $node = $node->withValue($value);
        }

        return $node;
    }
}
