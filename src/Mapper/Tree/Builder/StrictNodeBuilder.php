<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\MissingNodeValue;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final class StrictNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private bool $flexible;

    public function __construct(NodeBuilder $delegate, bool $flexible)
    {
        $this->delegate = $delegate;
        $this->flexible = $flexible;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        if (! $this->flexible) {
            TypeHelper::checkPermissiveType($shell->type());
        }

        if (! $shell->hasValue()) {
            if ($this->flexible && $shell->type()->accepts(null)) {
                return TreeNode::leaf($shell, null);
            }

            throw new MissingNodeValue($shell->type());
        }

        return $this->delegate->build($shell, $rootBuilder);
    }
}
