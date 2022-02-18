<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotPublic;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotStatic;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodClassReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\MethodNotFound;
use CuyZ\Valinor\Mapper\Object\Exception\MissingMethodArgument;
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
        $values = [];

        foreach ($this->method->parameters() as $parameter) {
            $name = $parameter->name();

            if (! array_key_exists($parameter->name(), $arguments) && ! $parameter->isOptional()) {
                throw new MissingMethodArgument($parameter);
            }

            if ($parameter->isVariadic()) {
                $values = [...$values, ...$arguments[$name]]; // @phpstan-ignore-line we know that the argument is iterable
            } else {
                $values[] = $arguments[$name];
            }
        }

        $className = $this->class->name();
        $methodName = $this->method->name();

        try {
            // @PHP8.0 `array_values` can be removed
            /** @infection-ignore-all */
            $values = array_values($values);

            if (! $this->method->isStatic()) {
                return new $className(...$values);
            }

            return $className::$methodName(...$values); // @phpstan-ignore-line
        } catch (Exception $exception) {
            throw ThrowableMessage::from($exception);
        }
    }
}
