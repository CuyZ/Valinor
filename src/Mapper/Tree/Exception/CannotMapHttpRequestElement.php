<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Mapper\Exception\MappingLogicalException;
use LogicException;

/** @internal */
final class CannotMapHttpRequestElement extends LogicException implements MappingLogicalException
{
    public function __construct(string $element)
    {
        parent::__construct("Element `$element` is not tagged with any of `#[FromRoute]`, `#[FromQuery]` or `#[FromBody]` attribute.");
    }
}
