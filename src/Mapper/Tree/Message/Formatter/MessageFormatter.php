<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Message\Formatter;

use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

/** @api */
interface MessageFormatter
{
    public function format(NodeMessage $message): NodeMessage;
}
