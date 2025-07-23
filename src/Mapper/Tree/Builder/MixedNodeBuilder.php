<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotMapToPermissiveType;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\MixedType;

/** @internal */
final class MixedNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        assert($shell->type() instanceof MixedType);

        if (! $shell->allowPermissiveTypes()) {
            throw new CannotMapToPermissiveType($shell);
        }

        return Node::new($shell->value());
    }
}
