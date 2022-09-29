<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ScalarType;

use function assert;

/** @internal */
final class ScalarNodeBuilder implements NodeBuilder
{
    private bool $flexible;

    public function __construct(bool $flexible)
    {
        $this->flexible = $flexible;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();
        $value = $shell->hasValue() ? $shell->value() : null;

        assert($type instanceof ScalarType);

        if (! $this->flexible || ! $type->canCast($value)) {
            throw $type->errorMessage();
        }

        return TreeNode::leaf($shell, $type->cast($value));
    }
}
