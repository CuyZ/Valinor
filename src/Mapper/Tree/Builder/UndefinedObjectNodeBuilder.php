<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Mapper\Tree\Exception\CannotMapToPermissiveType;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\UndefinedObjectType;

use function assert;
use function is_object;

/** @internal */
final class UndefinedObjectNodeBuilder implements NodeBuilder
{
    public function build(Shell $shell): Node
    {
        assert($shell->type instanceof UndefinedObjectType);

        if (! $shell->allowPermissiveTypes) {
            throw new CannotMapToPermissiveType($shell);
        }

        $value = $shell->value();

        if (! is_object($value)) {
            return $shell->error(new InvalidNodeValue());
        }

        return $shell->node($value);
    }
}
