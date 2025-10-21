<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Type\ObjectType;
use Exception;

use function array_map;
use function array_shift;
use function array_values;

/** @internal */
final class FunctionObjectBuilder implements ObjectBuilder
{
    private FunctionObject $function;

    private string $className;

    private Arguments $arguments;

    private bool $isDynamicConstructor;

    public function __construct(FunctionObject $function, ObjectType $type)
    {
        $definition = $function->definition;

        $arguments = array_map(
            fn (ParameterDefinition $parameter) => Argument::fromParameter($parameter),
            array_values([...$definition->parameters])
        );

        $this->isDynamicConstructor = $definition->attributes->has(DynamicConstructor::class);

        if ($this->isDynamicConstructor) {
            array_shift($arguments);
        }

        $this->function = $function;
        $this->className = $type->className();
        $this->arguments = new Arguments(...$arguments);
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
