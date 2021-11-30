<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotPublic;
use CuyZ\Valinor\Mapper\Object\Exception\ConstructorMethodIsNotStatic;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodClassReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidConstructorMethodReturnType;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSourceForObject;
use CuyZ\Valinor\Mapper\Object\Exception\MethodNotFound;
use CuyZ\Valinor\Mapper\Object\Exception\MissingMethodArgument;
use CuyZ\Valinor\Mapper\Object\Exception\ObjectConstructionError;
use CuyZ\Valinor\Type\Types\ClassType;
use Exception;

use function array_key_exists;
use function array_keys;
use function array_values;
use function count;
use function is_a;
use function is_array;
use function is_iterable;
use function iterator_to_array;

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

    public function describeArguments($source): iterable
    {
        $source = $this->transformSource($source);

        foreach ($this->method->parameters() as $parameter) {
            $name = $parameter->name();
            $type = $parameter->type();
            $attributes = $parameter->attributes();
            $value = array_key_exists($name, $source) ? $source[$name] : $parameter->defaultValue();

            yield new Argument($name, $type, $value, $attributes);
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
            if (! $this->method->isStatic()) {
                // @PHP8.0 `array_values` can be removed
                /** @infection-ignore-all */
                return new $className(...array_values($arguments));
            }

            // @PHP8.0 `array_values` can be removed
            /** @infection-ignore-all */
            return $className::$methodName(...array_values($arguments)); // @phpstan-ignore-line
        } catch (Exception $exception) {
            throw new ObjectConstructionError($exception);
        }
    }

    /**
     * @param mixed $source
     * @return mixed[]
     */
    private function transformSource($source): array
    {
        if ($source === null) {
            return [];
        }

        if (is_iterable($source) && ! is_array($source)) {
            $source = iterator_to_array($source);
        }

        $parameters = $this->method->parameters();

        if (count($parameters) === 1) {
            $name = array_keys(iterator_to_array($parameters))[0];

            if (! is_array($source) || ! array_key_exists($name, $source)) {
                $source = [$name => $source];
            }
        }

        if (! is_array($source)) {
            throw new InvalidSourceForObject($source);
        }

        return $source;
    }
}
