<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidResolvedImplementationValue;
use CuyZ\Valinor\Mapper\Tree\Exception\MissingObjectImplementationRegistration;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationNotRegistered;
use CuyZ\Valinor\Mapper\Tree\Exception\ResolvedImplementationIsNotAccepted;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\UnionType;
use Exception;

use function assert;
use function count;
use function is_string;

/** @internal */
final class InterfaceInferringContainer
{
    /** @var array<class-string, non-empty-array<string, ClassType>> */
    private array $implementations = [];

    public function __construct(
        private FunctionsContainer $functions,
        private TypeParser $typeParser
    ) {}

    /**
     * @param class-string $name
     */
    public function has(string $name): bool
    {
        return $this->functions->has($name);
    }

    /**
     * @param class-string $name
     */
    public function inferFunctionFor(string $name): FunctionDefinition
    {
        return $this->functions->get($name)->definition;
    }

    /**
     * @param class-string $name
     * @param array<mixed> $arguments
     */
    public function inferClassFor(string $name, array $arguments): ClassType
    {
        $class = $this->call($name, $arguments);
        $implementations = $this->classImplementationsFor($name);

        return $implementations[$class]
            ?? throw new ObjectImplementationNotRegistered($class, $name, $implementations);
    }

    /**
     * @param class-string $name
     * @return non-empty-array<string, ClassType>
     */
    public function classImplementationsFor(string $name): array
    {
        if (isset($this->implementations[$name])) {
            // @infection-ignore-all / This is a performance optimization, not easily testable so we skip it.
            return $this->implementations[$name];
        }

        $function = $this->functions->get($name)->definition;

        $type = $this->typeParser->parse($name);

        /** @infection-ignore-all */
        assert($type instanceof InterfaceType || $type instanceof ClassType);

        $classes = $this->implementationsByReturnSignature($name, $function);

        if ($classes === []) {
            throw new MissingObjectImplementationRegistration($name, $function);
        }

        foreach ($classes as $classType) {
            if (! $classType instanceof ClassType || ! $classType->matches($type)) {
                throw new ResolvedImplementationIsNotAccepted($name, $classType);
            }
        }

        /** @var non-empty-array<string, ClassType> $classes */
        return $this->implementations[$name] = $classes;
    }

    /**
     * @param mixed[] $arguments
     */
    private function call(string $name, array $arguments): string
    {
        try {
            $signature = ($this->functions->get($name)->callback)(...$arguments);
        } catch (Exception $exception) {
            throw new ObjectImplementationCallbackError($exception);
        }

        if (! is_string($signature)) {
            throw new InvalidResolvedImplementationValue($name, $signature);
        }

        return $signature;
    }

    /**
     * @return array<string, Type>
     */
    private function implementationsByReturnSignature(string $name, FunctionDefinition $function): array
    {
        $returnType = $function->returnType;

        if (! $returnType instanceof ClassStringType && ! $returnType instanceof UnionType) {
            if (count($function->parameters) > 0) {
                return [];
            }

            $class = $this->call($name, []);
            $classType = $this->typeParser->parse($class);

            return [$classType->toString() => $classType];
        }

        $types = $returnType instanceof UnionType
            ? $returnType->types()
            : [$returnType];

        $classes = [];

        foreach ($types as $type) {
            if (! $type instanceof ClassStringType) {
                return [];
            }

            $subTypes = $type->subTypes();

            if ($subTypes === []) {
                return [];
            }

            foreach ($subTypes as $classType) {
                $classes[$classType->toString()] = $classType;
            }
        }

        return $classes;
    }
}
