<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\FloatType;
use CuyZ\Valinor\Type\ScalarType;

use function assert;
use function is_int;

/** @internal */
final class ScalarNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell): Node
    {
        $type = $shell->type;
        $value = $shell->value();

        assert($type instanceof ScalarType);

        // When the value is an integer and the type is a float, the value is
        // cast to float, to follow the rule of PHP regarding acceptance of an
        // integer value in a float type. Note that PHPStan/Psalm analysis
        // applies the same rule.
        if ($type instanceof FloatType && is_int($value)) {
            $value = (float)$value;
        }

        if ($type->accepts($value)) {
            return $shell->node($value);
        }

        if (! $shell->allowScalarValueCasting || ! $type->canCast($value)) {
            return $shell->error($type->errorMessage());
        }

        return $shell->node($type->cast($value));
    }
}
