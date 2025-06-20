<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\SourceIsNotNull;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\NullType;

use function assert;

/** @internal */
final class NullNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof NullType);

        if ($value !== null) {
            return Node::error($shell, new SourceIsNotNull());
        }

        return Node::new(null);
    }
}
