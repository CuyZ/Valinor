<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Tree\Builder\Node;
use Exception;

/** @internal */
final class InvalidNodeDuringValueConversion extends Exception
{
    public function __construct(
        public readonly Node $node,
    ) {
        // @infection-ignore-all
        parent::__construct();
    }
}
