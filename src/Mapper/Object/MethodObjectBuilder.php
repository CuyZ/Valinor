<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotPublic;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotStatic;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodClassReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\MethodNotFound;
use CuyZ\Valinor\Mapper\Object\Exception\MissingMethodArgument;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use CuyZ\Valinor\Type\Types\ClassType;
use Exception;

use function array_values;
use function is_a;

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

        $returnType = $this->method->returnType();

        if (! $returnType instanceof ClassType) {
            throw new InvalidConstructorMethodReturnType($this->method, $this->class->name());
        }

        if (! is_a($returnType->signature()->className(), $this->class->name(), true)) {
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
        foreach ($this->method->parameters() as $parameter) {
            if (! array_key_exists($parameter->name(), $arguments) && ! $parameter->isOptional()) {
                throw new MissingMethodArgument($parameter);
            }
        }

        $className = $this->class->name();
        $methodName = $this->method->name();

        try {
            // @PHP8.0 `array_values` can be removed
            $arguments = array_values($arguments);

            if (! $this->method->isStatic()) {
                /** @infection-ignore-all */
                return new $className(...$arguments);
            }

            /** @infection-ignore-all */
            return $className::$methodName(...$arguments); // @phpstan-ignore-line
        } catch (Exception $exception) {
            throw ThrowableMessage::from($exception);
        }
    }
}
