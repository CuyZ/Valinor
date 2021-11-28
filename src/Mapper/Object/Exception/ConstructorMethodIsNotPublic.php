<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\MethodDefinition;
use LogicException;

final class ConstructorMethodIsNotPublic extends LogicException
{
    public function __construct(MethodDefinition $method)
    {
        parent::__construct(
            "The constructor method `{$method->signature()}` must be public.",
            1630937169
        );
    }
}
