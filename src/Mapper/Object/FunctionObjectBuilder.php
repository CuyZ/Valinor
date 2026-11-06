<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Type\ObjectType;
use Exception;

/** @internal */
final class FunctionObjectBuilder implements ObjectBuilder
{
    private FunctionObject $function;

    private string $className;

    private Arguments $arguments;

    private bool $isDynamicConstructor;

    public function __construct(FunctionObject $function, ObjectType $type)
    {
        $this->isDynamicConstructor = $function->definition->attributes->has(DynamicConstructor::class);

        $parameters = $function->definition->parameters;

        if ($this->isDynamicConstructor) {
            $parameters = $parameters->shift();
        }

        $this->function = $function;
        $this->className = $type->className();
        $this->arguments = Arguments::fromParameters($parameters);
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments;
    }

    public function buildObject(array $arguments): object
    {
        $parameters = $this->function->definition->parameters;

        if ($this->isDynamicConstructor) {
            $arguments[$parameters->at(0)->name] = $this->className;
        }

        $arguments = new MethodArguments($parameters, $arguments);

        try {
            /** @var object */
            return ($this->function->callback)(...$arguments);
        } catch (Exception $exception) {
            throw UserlandError::from($exception);
        }
    }

    public function signature(): string
    {
        return $this->function->definition->signature;
    }
}
