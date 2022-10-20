<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use ReflectionClass;
use ReflectionFunction;
use Reflector;

/** @internal */
final class AliasSpecification
{
    public function __construct(
        /** @var ReflectionClass<object>|ReflectionFunction */
        private Reflector $reflection
    ) {
    }

    /**
     * @return ReflectionClass<object>|ReflectionFunction
     */
    public function reflection(): Reflector
    {
        return $this->reflection;
    }
}
