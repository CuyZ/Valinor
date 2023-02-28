<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\Type;
use Exception;

use function array_map;
use function array_shift;

/** @internal */
final class FunctionObjectBuilder implements ObjectBuilder
{
    private FunctionObject $function;

    private string $className;

    /**
     * @var array<string, Type>
     */
    private array $generics;

    private Arguments $arguments;

    private bool $isDynamicConstructor;

    private bool $isGenericConstructor;

    public function __construct(FunctionObject $function, ClassType $type)
    {
        $definition = $function->definition();

        $arguments = array_map(
            fn (ParameterDefinition $parameter) => Argument::fromParameter($parameter),
            array_values(iterator_to_array($definition->parameters())) // PHP8.1 array unpacking
        );

        $this->isDynamicConstructor = $definition->attributes()->has(DynamicConstructor::class);
        $this->isGenericConstructor = $type instanceof GenericType && count($type->generics()) > 0;

        if ($this->isDynamicConstructor || $this->isGenericConstructor) {
            array_shift($arguments);
        }

        $this->function = $function;
        $this->className = $type->className();
        $this->arguments = new Arguments(...$arguments);
        $this->generics = $type instanceof GenericType ? $type->generics() : [];
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments;
    }

    public function build(array $arguments): object
    {
        $parameters = $this->function->definition()->parameters();

        if ($this->isDynamicConstructor) {
            $arguments[$parameters->at(0)->name()] = $this->className;
        }

        if ($this->isGenericConstructor) {
            $arguments[$parameters->at(0)->name()] = $this->generics;
        }

        $arguments = new MethodArguments($parameters, $arguments);

        try {
            return ($this->function->callback())(...$arguments);
        } catch (Exception $exception) {
            throw UserlandError::from($exception);
        }
    }

    public function signature(): string
    {
        return $this->function->definition()->signature();
    }
}
