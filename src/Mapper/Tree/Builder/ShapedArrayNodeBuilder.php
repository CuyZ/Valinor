<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\SourceMustBeIterable;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedKeyInSource;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\ShapedArrayType;
use CuyZ\Valinor\Type\Types\ShapedListType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Type\VacantType;

use function array_diff_key;
use function array_key_exists;
use function assert;
use function count;
use function is_array;
use function is_int;
use function is_iterable;
use function iterator_to_array;

/** @internal */
final class ShapedArrayNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell): Node
    {
        $type = $shell->type;
        $value = $shell->value();

        assert($type instanceof ShapedArrayType || $type instanceof ShapedListType);

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
            $hasValue = array_key_exists($key, $value);

            if (! $hasValue && $element->isOptional()) {
                continue;
            }

            $child = $shell
                ->child((string)$key, $element->type())
                ->withAttributes($element->attributes());

            if ($hasValue) {
                $child = $child->withValue($value[$key]);
            }

            $child = $child->build();

            if ($child->isValid()) {
                $children[$key] = $child->value();
            } else {
                $errors[] = $child;
            }

            unset($value[$key]);
        }

        // Second phase: if the shaped array/list is unsealed, we take the
        // remaining values from the source and try to build them.
        if ($type->isUnsealed()) {
            if ($type instanceof ShapedListType) {
                $unsealedType = $type->unsealedType();
                $elementType = $unsealedType instanceof VacantType
                    ? MixedType::get()
                    : $unsealedType->subType();
                $expectedKey = count($type->elements);

                foreach ($value as $key => $val) {
                    if (! is_int($key) || $key !== $expectedKey) {
                        $errors[] = $shell
                            ->child((string)$key, UnresolvableType::forSuperfluousValue())
                            ->withValue($val)
                            ->error(new UnexpectedKeyInSource());
                        continue;
                    }

                    $expectedKey++;

                    $child = $shell
                        ->child((string)$key, $elementType)
                        ->withValue($val)
                        ->build();

                    if ($child->isValid()) {
                        $children[$key] = $child->value();
                    } else {
                        $errors[] = $child;
                    }
                }
            } else {
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
            }
        } elseif (! $shell->allowSuperfluousKeys) {
            // Third phase: the superfluous keys are not allowed, so we add an
            // error for each remaining key in the source.
            $diff = array_diff_key($value, $children, $shell->allowedSuperfluousKeys);

            foreach ($diff as $key => $val) {
                $errors[] = $shell
                    ->child((string)$key, UnresolvableType::forSuperfluousValue())
                    ->withValue($val)
                    ->error(new UnexpectedKeyInSource());
            }
        }

        if ($errors === []) {
            return $shell->node($children);
        }

        return $shell->errors($errors);
    }
}
