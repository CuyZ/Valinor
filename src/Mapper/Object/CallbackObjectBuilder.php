<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Mapper\Tree\Message\ThrowableMessage;
use Exception;

use function array_values;

/** @internal */
final class CallbackObjectBuilder implements ObjectBuilder
{
    private FunctionDefinition $function;

    /** @var callable(): object */
    private $callback;

    /**
     * @param callable(): object $callback
     */
    public function __construct(FunctionDefinition $function, callable $callback)
    {
        $this->function = $function;
        $this->callback = $callback;
    }

    public function describeArguments(): iterable
    {
        foreach ($this->function->parameters() as $parameter) {
            $argument = $parameter->isOptional()
                ? Argument::optional($parameter->name(), $parameter->type(), $parameter->defaultValue())
                : Argument::required($parameter->name(), $parameter->type());

            yield $argument->withAttributes($parameter->attributes());
        }
    }

    public function build(array $arguments): object
    {
        // @PHP8.0 `array_values` can be removed
        /** @infection-ignore-all */
        $arguments = array_values($arguments);

        try {
            return ($this->callback)(...$arguments);
        } catch (Exception $exception) {
            throw ThrowableMessage::from($exception);
        }
    }
}
