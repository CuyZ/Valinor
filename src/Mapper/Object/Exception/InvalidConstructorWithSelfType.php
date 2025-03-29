<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use LogicException;

/** @internal */
final class InvalidConstructorWithSelfType extends LogicException
{
    public function __construct(string $argumentSignature)
    {
        parent::__construct(
            "Circular dependency detected for `$argumentSignature`.",
            1739903374,
        );
    }
}
