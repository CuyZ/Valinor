<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class ValueAlteringNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private FunctionsContainer $functions;

    public function __construct(NodeBuilder $delegate, FunctionsContainer $functions)
    {
        $this->delegate = $delegate;
        $this->functions = $functions;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $node = $this->delegate->build($shell, $rootBuilder);

        if (! $node->isValid()) {
            return $node;
        }

        $value = $node->value();
        $type = $node->type();

        foreach ($this->functions as $function) {
            $parameters = $function->parameters();

            if (count($parameters) === 0) {
                continue;
            }

            if ($parameters->at(0)->type()->matches($type)) {
                $value = ($this->functions->callback($function))($value);
                $node = $node->withValue($value);
            }
        }

        return $node;
    }
}
