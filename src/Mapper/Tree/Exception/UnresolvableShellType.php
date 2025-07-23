<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Type\Types\UnresolvableType;
use LogicException;

/** @internal */
final class UnresolvableShellType extends LogicException
{
    public function __construct(UnresolvableType $type)
    {
        parent::__construct($type->message());
    }
}
