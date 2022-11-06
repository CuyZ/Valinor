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
        $type = $shell->type();

        if (! $this->flexible) {
            TypeHelper::checkPermissiveType($type);
        }

        if (! $shell->hasValue()) {
            if ($this->flexible) {
                return $this->delegate->build($shell->withValue(null), $rootBuilder);
            }

            throw new MissingNodeValue($type);
        }

        return $this->delegate->build($shell, $rootBuilder);
    }
}
