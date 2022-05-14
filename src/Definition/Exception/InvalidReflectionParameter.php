<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Definition\Exception;

use LogicException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Reflector;

use function get_class;
use function implode;

/** @internal */
final class InvalidReflectionParameter extends LogicException
{
    public function __construct(Reflector $reflector)
    {
        $class = get_class($reflector);
        $allowed = implode('`, `', [ReflectionClass::class, ReflectionProperty::class, ReflectionMethod::class]);

        parent::__construct(
            "Invalid parameter given (type `$class`), it must be an instance of `$allowed`.",
            1534263918
        );
    }
}
