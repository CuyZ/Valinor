<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use RuntimeException;

/** @internal */
final class ObjectBuildersCollision extends RuntimeException
{
    public function __construct(ObjectBuilder $builderA, ObjectBuilder $builderB)
    {
        parent::__construct(
            "A type collision was detected between the constructors `{$builderA->signature()}` and `{$builderB->signature()}`.",
        );
    }
}
