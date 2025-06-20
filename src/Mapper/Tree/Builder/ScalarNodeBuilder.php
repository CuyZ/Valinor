<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ScalarType;

use function assert;

/** @internal */
final class ScalarNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ScalarType);

        if ($type->accepts($value)) {
            return Node::new($value);
        }

        if (! $shell->allowScalarValueCasting() || ! $type->canCast($value)) {
            return Node::error($shell, $type->errorMessage());
        }

        return Node::new($type->cast($value));
    }
}
