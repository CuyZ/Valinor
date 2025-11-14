<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Exception\MappingLogicalException;
use LogicException;

/** @internal */
final class CannotMapHttpRequestToUnsealedShapedArray extends LogicException implements MappingLogicalException
{
    protected $message = 'Mapping an HTTP request to an unsealed shaped array is not supported.';

}
