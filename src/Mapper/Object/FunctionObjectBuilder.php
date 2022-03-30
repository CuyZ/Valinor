<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\ParameterDefinition;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use Exception;

use function array_map;
use function array_values;
use function iterator_to_array;

/** @internal */
final class FunctionObjectBuilder implements ObjectBuilder
{
    private FunctionDefinition $function;

    /** @var callable(): object */
    private $callback;

    private Arguments $arguments;

    /**
     * @param callable(): object $callback
     */
    public function __construct(FunctionDefinition $function, callable $callback)
    {
        $this->function = $function;
        $this->callback = $callback;
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments ??= new Arguments(
            ...array_map(function (ParameterDefinition $parameter) {
                $argument = $parameter->isOptional()
                    ? Argument::optional($parameter->name(), $parameter->type(), $parameter->defaultValue())
                    : Argument::required($parameter->name(), $parameter->type());

                return $argument->withAttributes($parameter->attributes());
            }, array_values(iterator_to_array($this->function->parameters()))) // @PHP8.1 array unpacking
        );
    }

    public function build(array $arguments): object
    {
        $arguments = new MethodArguments($this->function->parameters(), $arguments);

        try {
            return ($this->callback)(...$arguments);
        } catch (Exception $exception) {
            throw ThrowableMessage::from($exception);
        }
    }
}
