<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper;

use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use Throwable;

/** @api */
interface MappingError extends Throwable
{
    public function messages(): Messages;
}
