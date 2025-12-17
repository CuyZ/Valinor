<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Exception\MappingLogicalException;
use LogicException;

/** @internal */
final class CannotUseBothFromQueryAttributes extends LogicException implements MappingLogicalException
{
    protected $message = 'Cannot use `#[FromQuery(mapAll: true)]` alongside other `#[FromQuery]` attributes.';
}
