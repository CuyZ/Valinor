<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Attribute;

use Attribute;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Mapper\Tree\Visitor\ShellVisitor;

/**
 * This attribute can be added to an object property. It will be filled with
 * the iteration key from the source which is used to map the object tree.
 *
 * ```php
 * use CuyZ\Valinor\Attribute\Identifier;
 *
 * final class Foo
 * {
 *     #[Identifier]
 *     private string $identifier;
 * }
 *
 * $source = [
 *     'my first key' => [...], // $identifier will be filled with `my first key`
 *     'my second key' => [...], // $identifier will be filled with `my second key`
 * ];
 * ```
 *
 * @api
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final class Identifier implements ShellVisitor
{
    public function visit(Shell $shell): Shell
    {
        return $shell->withValue($shell->parent()->name());
    }
}
