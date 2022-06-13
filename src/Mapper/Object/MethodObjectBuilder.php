<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotPublic;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotStatic;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodClassReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\MethodNotFound;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use Exception;

/** @api */
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

        if ($this->method->name() === '__construct') {
            return;
        }

        if (! $this->method->isStatic()) {
            throw new ConstructorMethodIsNotStatic($this->method);
        }

        if (! $this->method->returnType()->matches($this->class->type())) {
            throw new InvalidConstructorMethodClassReturnType($this->method, $this->class->name());
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
            return $this->method->isStatic()
                ? $className::$methodName(...$arguments) // @phpstan-ignore-line
                : new $className(...$arguments);
        } catch (Exception $exception) {
            throw ThrowableMessage::from($exception);
        }
    }

    public function signature(): string
    {
        return $this->method->signature();
    }
}
