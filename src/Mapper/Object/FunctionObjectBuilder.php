<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use Exception;

/** @internal */
final class FunctionObjectBuilder implements ObjectBuilder
{
    private FunctionObject $function;

    private Arguments $arguments;

    public function __construct(FunctionObject $function)
    {
        $this->function = $function;
    }

    public function describeArguments(): Arguments
    {
        return $this->arguments ??= Arguments::fromParameters($this->function->definition()->parameters());
    }

    public function build(array $arguments): object
    {
        $arguments = new MethodArguments($this->function->definition()->parameters(), $arguments);

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
