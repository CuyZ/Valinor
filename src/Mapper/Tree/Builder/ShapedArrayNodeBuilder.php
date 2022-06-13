<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\ShapedArrayElementMissing;
use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedShapedArrayKeys;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

use function array_key_exists;
use function array_keys;
use function assert;
use function count;
use function is_array;

/** @internal */
final class ShapedArrayNodeBuilder implements NodeBuilder
{
    private bool $flexible;

    public function __construct(bool $flexible)
    {
        $this->flexible = $flexible;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->hasValue() ? $shell->value() : null;

        assert($type instanceof ShapedArrayType);

        if (! is_array($value)) {
            throw new SourceMustBeIterable($value, $type);
        }

        $children = $this->children($type, $shell, $rootBuilder);
        $array = $this->buildArray($children);

        return Node::branch($shell, $array, $children);
    }

    /**
     * @return array<Node>
     */
    private function children(ShapedArrayType $type, Shell $shell, RootNodeBuilder $rootBuilder): array
    {
        /** @var array<mixed> $value */
        $value = $shell->value();
        $elements = $type->elements();
        $children = [];

        foreach ($elements as $element) {
            /** @var string|int $key */
            $key = $element->key()->value();

            $child = $shell->child((string)$key, $element->type());

            if (! array_key_exists($key, $value)) {
                if (! $element->isOptional()) {
                    $children[$key] = Node::error($child, new ShapedArrayElementMissing($element));
                }

                continue;
            }

            $child = $child->withValue($value[$key]);
            $children[$key] = $rootBuilder->build($child);

            unset($value[$key]);
        }

        if (! $this->flexible && count($value) > 0) {
            throw new UnexpectedShapedArrayKeys(array_keys($value), $elements);
        }

        return $children;
    }

    /**
     * @param array<Node> $children
     * @return mixed[]|null
     */
    private function buildArray(array $children): ?array
    {
        $array = [];

        foreach ($children as $key => $child) {
            if (! $child->isValid()) {
                return null;
            }

            $array[$key] = $child->value();
        }

        return $array;
    }
}
