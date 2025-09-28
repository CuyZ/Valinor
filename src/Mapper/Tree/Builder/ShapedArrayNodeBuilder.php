<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedKeysInSource;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;

use function array_key_exists;
use function assert;
use function is_array;
use function is_iterable;

/** @internal */
final class ShapedArrayNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell): Node
    {
        $type = $shell->type;
        $value = $shell->value();

        assert($type instanceof ShapedArrayType);

        if (! is_iterable($value)) {
            return $shell->error(new SourceMustBeIterable($value));
        }

        $children = [];
        $childrenNames = [];
        $errors = [];

        if (! is_array($value)) {
            $value = iterator_to_array($value);
        }

        foreach ($type->elements as $key => $element) {
            $childrenNames[] = $key;

            $child = $shell->child((string)$key, $element->type());
            $child = $child->withAttributes($element->attributes());

            if (array_key_exists($key, $value)) {
                $child = $child->withValue($value[$key]);
            } elseif ($element->isOptional()) {
                continue;
            }

            $child = $child->build();

            if (! $child->isValid()) {
                $errors[] = $child;
            } else {
                $children[$key] = $child->value();
            }

            unset($value[$key]);
        }

        if ($type->isUnsealed) {
            $unsealedNode = $shell
                ->withType($type->unsealedType())
                ->withValue($value)
                ->build();

            if (! $unsealedNode->isValid()) {
                $errors[] = $unsealedNode;
            } else {
                // @phpstan-ignore assignOp.invalid (we know value is an array)
                $children += $unsealedNode->value();
            }
        }

        if ($errors === []) {
            $node = $shell->node($children);
        } else {
            $node = $shell->errors($errors);
        }

        if (! $type->isUnsealed) {
            $node = $this->checkUnexpectedKeys($shell, $node, $childrenNames);
        }

        return $node;
    }

    /**
     * @param list<int|string> $children
     */
    private function checkUnexpectedKeys(Shell $shell, Node $node, array $children): Node
    {
        $value = $shell->value();

        if ($shell->allowSuperfluousKeys || ! is_array($value)) {
            return $node;
        }

        $diff = array_diff(array_keys($value), $children, $shell->allowedSuperfluousKeys);

        if ($diff === []) {
            return $node;
        }

        /** @var non-empty-list<int|string> $children */
        $error = new UnexpectedKeysInSource($value, $children);

        $nodeMessage = new NodeMessage(
            $error,
            $error->body(),
            $shell->name,
            $shell->path,
            "`{$shell->type->toString()}`",
            $shell->expectedSignature(),
            $shell->dumpValue(),
        );

        return $node->appendMessage($nodeMessage);
    }
}
