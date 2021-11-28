<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Printer;

use CuyZ\Valinor\Mapper\Tree\Node;

interface TreePrinter
{
    public function print(Node $node): string;
}
