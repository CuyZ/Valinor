<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\MissingNodeValue;
use CuyZ\Valinor\Mapper\Tree\Node;
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

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        if (! $this->flexible) {
            TypeHelper::checkPermissiveType($shell->type());
        }

        if (! $shell->hasValue()) {
            if (! $this->flexible) {
                throw new MissingNodeValue($shell->type());
            }

            $shell = $shell->withValue(null);
        }

        return $this->delegate->build($shell, $rootBuilder);
    }
}
