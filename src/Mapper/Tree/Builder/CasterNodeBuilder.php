<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\NoCasterForType;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

final class CasterNodeBuilder implements NodeBuilder
{
    /** @var array<class-string, NodeBuilder> */
    private array $builders;

    /**
     * @param array<class-string, NodeBuilder> $builders
     */
    public function __construct(array $builders)
    {
        $this->builders = $builders;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        if ($type->accepts($value)) {
            return Node::leaf($shell, $value);
        }

        foreach ($this->builders as $allowed => $builder) {
            if ($type instanceof $allowed) {
                return $builder->build($shell, $rootBuilder);
            }
        }

        throw new NoCasterForType($type);
    }
}
