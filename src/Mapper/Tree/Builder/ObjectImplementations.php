<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidAbstractObjectName;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidResolvedImplementationValue;
use CuyZ\Valinor\Mapper\Tree\Exception\MissingObjectImplementationRegistration;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationNotRegistered;
use CuyZ\Valinor\Mapper\Tree\Exception\ResolvedImplementationIsNotAccepted;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassStringType;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\UnionType;
use Exception;

/** @internal */
final class ObjectImplementations
{
    /** @var array<string, non-empty-array<string, ClassType>> */
    private array $implementations = [];

    private FunctionsContainer $functions;

    private TypeParser $typeParser;

    public function __construct(FunctionsContainer $functions, TypeParser $typeParser)
    {
        $this->typeParser = $typeParser;
        $this->functions = $functions;

        foreach ($functions as $name => $function) {
            $this->implementations[(string)$name] = $this->implementations((string)$name);
        }
    }

    public function function(string $name): FunctionDefinition
    {
        if (! $this->functions->has($name)) {
            throw new CannotResolveObjectType($name);
        }

        return $this->functions->get($name);
    }

    /**
     * @param mixed[] $arguments
     */
    public function implementation(string $name, array $arguments): ClassType
    {
        $class = $this->call($name, $arguments);

        if (! isset($this->implementations[$name][$class])) {
            // @PHP8.0 use throw exception expression
            throw new ObjectImplementationNotRegistered($class, $name, $this->implementations[$name]);
        }

        return $this->implementations[$name][$class];
    }

    /**
     * @param mixed[] $arguments
     */
    private function call(string $name, array $arguments): string
    {
        $function = $this->functions->get($name);
        $callback = $this->functions->callback($function);

        try {
            $signature = $callback(...$arguments);
        } catch (Exception $exception) {
            throw new ObjectImplementationCallbackError($name, $exception);
        }

        if (! is_string($signature)) {
            throw new InvalidResolvedImplementationValue($name, $signature);
        }

        return $signature;
    }

    /**
     * @return non-empty-array<string, ClassType>
     */
    private function implementations(string $name): array
    {
        $function = $this->functions->get($name);

        try {
            $type = $this->typeParser->parse($name);
        } catch (InvalidType $exception) {
            // @PHP8.0 remove variable
        }

        if (! isset($type) || ! $type instanceof InterfaceType) {
            throw new InvalidAbstractObjectName($name);
        }

        $classes = $this->implementationsByReturnSignature($name, $function);

        if (empty($classes)) {
            throw new MissingObjectImplementationRegistration($name, $function);
        }

        foreach ($classes as $classType) {
            if (! $classType instanceof ClassType || ! $type->matches($classType)) {
                throw new ResolvedImplementationIsNotAccepted($name, $classType);
            }
        }

        /** @var non-empty-array<string, ClassType> $classes */
        return $classes;
    }

    /**
     * @return array<string, Type>
     */
    private function implementationsByReturnSignature(string $name, FunctionDefinition $function): array
    {
        $returnType = $function->returnType();

        if (! $returnType instanceof ClassStringType && ! $returnType instanceof UnionType) {
            if (count($function->parameters()) > 0) {
                return [];
            }

            $class = $this->call($name, []);
            $classType = $this->typeParser->parse($class);

            return [(string)$classType => $classType];
        }

        $types = $returnType instanceof UnionType
            ? $returnType->types()
            : [$returnType];

        $classes = [];

        foreach ($types as $type) {
            if (! $type instanceof ClassStringType) {
                return [];
            }

            $subType = $type->subType();

            if ($subType === null) {
                return [];
            }

            $subTypes = $subType instanceof UnionType
                ? $subType->types()
                : [$subType];

            foreach ($subTypes as $classType) {
                $classes[(string)$classType] = $classType;
            }
        }

        return $classes;
    }
}
