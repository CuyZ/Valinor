<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Node;
use Throwable;

/** @api */
interface MappingError extends Throwable
{
    public function node(): Node;
}
