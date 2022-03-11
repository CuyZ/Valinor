<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Exception;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use LogicException;

/** @internal */
final class ConstructorMethodIsNotPublic extends LogicException
{
    public function __construct(ClassDefinition $class, MethodDefinition $method)
    {
        $message = $method->name() === '__construct'
            ? "The constructor of the class `{$class->name()}` is not public."
            : "The named constructor `{$method->signature()}` is not public.";

        parent::__construct($message, 1630937169);
    }
}
