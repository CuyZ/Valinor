<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\MissingNodeValue;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Utility\TypeHelper;

/** @internal */
final class StrictNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
    ) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        if (! $shell->allowPermissiveTypes()) {
            TypeHelper::checkPermissiveType($type);
        }

        if (! $shell->hasValue()) {
            if ($shell->enableFlexibleCasting()) {
                return $this->delegate->build($shell->withValue(null), $rootBuilder);
            }

            throw new MissingNodeValue($type);
        }

        return $this->delegate->build($shell, $rootBuilder);
    }
}
