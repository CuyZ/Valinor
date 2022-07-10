<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Exception;

use CuyZ\Valinor\Type\Type;
use RuntimeException;

/** @internal */
final class NoCasterForType extends RuntimeException
{
    public function __construct(Type $type)
    {
        parent::__construct(
            "No caster was found to convert to type `{$type->toString()}`.",
            1630693475
        );
    }
}
