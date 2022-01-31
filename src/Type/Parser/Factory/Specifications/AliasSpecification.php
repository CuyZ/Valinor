<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory\Specifications;

use ReflectionClass;
use ReflectionFunction;
use Reflector;

/** @internal */
final class AliasSpecification
{
    /** @var ReflectionClass<object>|ReflectionFunction */
    private Reflector $reflection;

    /**
     * @param ReflectionClass<object>|ReflectionFunction $reflection
     */
    public function __construct(Reflector $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * @return ReflectionClass<object>|ReflectionFunction
     */
    public function reflection(): Reflector
    {
        return $this->reflection;
    }
}
