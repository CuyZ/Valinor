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

    public function __construct(ClassDefinition $class, string $methodName)
    {
        $methods = $class->methods();

        if (! $methods->has($methodName)) {
            throw new MethodNotFound($class, $methodName);
        }

        $this->class = $class;
        $this->method = $methods->get($methodName);

        if (! $this->method->isPublic()) {
            throw new ConstructorMethodIsNotPublic($this->method);
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

    public function describeArguments(): iterable
    {
        foreach ($this->method->parameters() as $parameter) {
            $argument = $parameter->isOptional()
                ? Argument::optional($parameter->name(), $parameter->type(), $parameter->defaultValue())
                : Argument::required($parameter->name(), $parameter->type());

            yield $argument->withAttributes($parameter->attributes());
        }
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
}
