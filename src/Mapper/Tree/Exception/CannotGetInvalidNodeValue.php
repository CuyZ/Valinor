<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Node;
use LogicException;

/** @internal */
final class CannotGetInvalidNodeValue extends LogicException
{
    public function __construct(Node $node)
    {
        parent::__construct(
            "Trying to get value of an invalid node at path `{$node->path()}`.",
            1_630_680_246
        );
    }
}
