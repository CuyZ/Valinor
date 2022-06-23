<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Utility\PermissiveTypeFound;
use LogicException;

/** @internal */
final class PermissiveTypeNotAllowed extends LogicException
{
    public function __construct(ObjectBuilder $builder, Argument $argument, PermissiveTypeFound $original)
    {
        parent::__construct(
            "Error for `{$argument->name()}` in `{$builder->signature()}`: {$original->getMessage()}",
            1655389255
        );
    }
}
