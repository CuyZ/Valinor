<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object;

use CuyZ\Valinor\Definition\Parameters;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use Exception;

/** @internal */
final class MethodObjectBuilder implements ObjectBuilder
{
    private Arguments $arguments;

    public function __construct(
        private string $className,
        private string $methodName,
        private Parameters $parameters
    ) {}

    public function describeArguments(): Arguments
    {
        return $this->arguments ??= Arguments::fromParameters($this->parameters);
    }

    public function buildObject(array $arguments): object
    {
        $methodName = $this->methodName;
        $arguments = new MethodArguments($this->parameters, $arguments);

        try {
            return ($this->className)::$methodName(...$arguments); // @phpstan-ignore-line
        } catch (Exception $exception) {
            throw UserlandError::from($exception);
        }
    }

    public function signature(): string
    {
        return "$this->className::$this->methodName()";
    }
}
