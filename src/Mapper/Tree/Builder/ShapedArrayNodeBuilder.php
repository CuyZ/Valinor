<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedKeyInSource;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\UnresolvableType;

use function array_diff_key;
use function array_key_exists;
use function assert;
use function is_array;
use function is_iterable;
use function iterator_to_array;

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

        if (! is_array($value)) {
            $value = iterator_to_array($value);
        }

        $children = [];
        $errors = [];

        // First phase: we loop through all the shaped array elements and try
        // to find corresponding value in the source value to build them.
        foreach ($type->elements as $key => $element) {
            $child = $shell
                ->child((string)$key, $element->type())
                ->withAttributes($element->attributes());

            if (array_key_exists($key, $value)) {
                $child = $child->withValue($value[$key]);
            } elseif ($element->isOptional()) {
                continue;
            }

            $child = $child->build();

            if ($child->isValid()) {
                $children[$key] = $child->value();
            } else {
                $errors[] = $child;
            }

            unset($value[$key]);
        }

        // Second phase: if the shaped array is unsealed, we take the remaining
        // values from the source and try to build them.
        if ($type->isUnsealed) {
            $unsealedNode = $shell
                ->withType($type->unsealedType())
                ->withValue($value)
                ->build();

            if ($unsealedNode->isValid()) {
                // @phpstan-ignore assignOp.invalid (we know value is an array)
                $children += $unsealedNode->value();
            } else {
                $errors[] = $unsealedNode;
            }
        } elseif (! $shell->allowSuperfluousKeys) {
            // Third phase: the superfluous keys are not allowed, so we add an
            // error for each remaining key in the source.
            $diff = array_diff_key($value, $children, $shell->allowedSuperfluousKeys);

            foreach ($diff as $key => $val) {
                $errors[] = $shell
                    ->child((string)$key, UnresolvableType::forSuperfluousValue((string)$key))
                    ->withValue($val)
                    ->error(new UnexpectedKeyInSource((string)$key));
            }
        }

        if ($errors === []) {
            return $shell->node($children);
        }

        return $shell->errors($errors);
    }
}
