<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionObject;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotInferFinalClass;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveObjectType;
use CuyZ\Valinor\Mapper\Tree\Exception\InterfaceHasBothConstructorAndInfer;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Utility\Polyfill;
use Throwable;

use function assert;

/** @internal */
final class InterfaceNodeBuilder implements NodeBuilder
{
    public function __construct(
        private InterfaceInferringContainer $interfaceInferringContainer,
        private FunctionsContainer $constructors,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function build(Shell $shell): Node
    {
        assert($shell->type instanceof InterfaceType || $shell->type instanceof NativeClassType);

        $function = $this->interfaceInferringContainer->inferFunctionFor($shell->type->className());

        $arguments = Arguments::fromParameters($function->parameters);
        $argumentsValues = ArgumentsValues::forInterface($shell, $arguments);

        $valuesNode = $argumentsValues->shell->build();

        if (! $valuesNode->isValid()) {
            $messages = $valuesNode->messages();

            if ($messages[0]->path() === '*root*') {
                return $shell->error($valuesNode->messages()[0]);
            }

            return $valuesNode;
        }

        try {
            $values = $argumentsValues->transform($valuesNode->value());

            $classType = $this->interfaceInferringContainer->inferClassFor($shell->type->className(), $values);
        } catch (ObjectImplementationCallbackError $exception) {
            $exception = ($this->exceptionFilter)($exception->original());

            return $shell->error($exception);
        }

        return $shell
            ->withType($classType)
            ->withAllowedSuperfluousKeys($arguments->names())
            ->shouldNotApplyConverters()
            ->build();
    }

    public function canInferImplementation(ClassDefinition $class): bool
    {
        $hasImplementation = $this->interfaceInferringContainer->has($class->name);

        $hasConstructor = Polyfill::array_any(
            $this->constructors->toArray(),
            static fn (FunctionObject $constructor) => $class->type->matches($constructor->definition->returnType),
        );

        if ($hasConstructor && $hasImplementation) {
            throw new InterfaceHasBothConstructorAndInfer($class->name);
        }

        if ($hasConstructor) {
            return false;
        }

        if ($hasImplementation) {
            if ($class->isFinal) {
                $function = $this->interfaceInferringContainer->inferFunctionFor($class->name);

                throw new CannotInferFinalClass($class->name, $function);
            }

            return true;
        }

        if ($class->type instanceof InterfaceType || $class->isAbstract) {
            throw new CannotResolveObjectType($class->name);
        }

        return false;
    }
}
