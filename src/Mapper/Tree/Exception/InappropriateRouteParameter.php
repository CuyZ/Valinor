<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use LogicException;

/** @internal */
final class InappropriateRouteParameter extends LogicException
{
    public function __construct(string $routeParameter)
    {
        parent::__construct(<<<TXT
        Route parameter `$routeParameter` was not provided in HTTP request.
        This is a logic error meaning either the router forgot to bind the route parameter, or there is an inappropriate `#[FromRoute]` parameter.
        TXT);
    }
}
