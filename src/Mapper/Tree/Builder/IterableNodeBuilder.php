<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;

use function is_array;
use function is_iterable;
use function iterator_to_array;

/** @internal */
final class IterableNodeBuilder implements NodeBuilder
{
    public function __construct(private NodeBuilder $delegate)
    {
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $value = $shell->value();

        if (is_iterable($value) && ! is_array($value)) {
            $shell = $shell->withValue(iterator_to_array($value));
        }

        return $this->delegate->build($shell, $rootBuilder);
    }
}
