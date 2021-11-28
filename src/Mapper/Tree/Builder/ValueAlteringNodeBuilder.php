<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

/**
 * @template T
 */
final class ValueAlteringNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    /** @var array<string, array<callable(T): T>> */
    private array $valueModifiers;

    /**
     * @param array<string, array<callable(T): T>> $valueModifiers
     */
    public function __construct(NodeBuilder $delegate, array $valueModifiers)
    {
        $this->delegate = $delegate;
        $this->valueModifiers = $valueModifiers;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $node = $this->delegate->build($shell, $rootBuilder);

        if (! $node->isValid()) {
            return $node;
        }

        /** @var T $value */
        $value = $node->value();
        $type = (string)$node->type();

        foreach ($this->valueModifiers[$type] ?? [] as $valueModifier) {
            $value = $valueModifier($value);
            $node = $node->withValue($value);
        }

        return $node;
    }
}
