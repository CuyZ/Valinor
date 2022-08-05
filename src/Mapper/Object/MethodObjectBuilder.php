<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotPublic;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotStatic;
use CuyZ\Valinor\Mapper\Object\Exception\MethodNotFound;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use Exception;

/** @internal */
final class MethodObjectBuilder implements ObjectBuilder
{
    private ClassDefinition $class;

    private MethodDefinition $method;

    private Arguments $arguments;

    public function __construct(ClassDefinition $class, string $methodName)
    {
        $methods = $class->methods();

        if (! $methods->has($methodName)) {
            throw new MethodNotFound($class, $methodName);
        }

        $this->class = $class;
        $this->method = $methods->get($methodName);

        if (! $this->method->isPublic()) {
            throw new ConstructorMethodIsNotPublic($this->class, $this->method);
        }

        if (! $this->method->isStatic()) {
            throw new ConstructorMethodIsNotStatic($this->method);
        }
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments ??= Arguments::fromParameters($this->method->parameters());
    }

    public function build(array $arguments): object
    {
        $className = $this->class->name();
        $methodName = $this->method->name();
        $arguments = new MethodArguments($this->method->parameters(), $arguments);

        try {
            return $className::$methodName(...$arguments); // @phpstan-ignore-line
        } catch (Exception $exception) {
            throw UserlandError::from($exception);
        }
    }

    public function signature(): string
    {
        return $this->method->signature();
    }
}
