<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\Exception\CannotFindConstructor;
use CuyZ\Valinor\Mapper\Object\Exception\SeveralConstructorsFound;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ReflectionObjectBuilder;

use function array_pop;
use function count;
use function is_array;
use function ksort;

/** @internal */
final class ConstructorObjectBuilderFactory implements ObjectBuilderFactory
{
    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        $constructors = $this->findConstructors($class);

        if (count($constructors) === 0) {
            return new ReflectionObjectBuilder($class);
        }

        if (count($constructors) === 1) {
            return new MethodObjectBuilder($class, $constructors[0]->name());
        }

        $constructor = $this->findUsableConstructor($constructors, $source);

        return new MethodObjectBuilder($class, $constructor->name());
    }

    /**
     * @return list<MethodDefinition>
     */
    private function findConstructors(ClassDefinition $class): array
    {
        $constructors = [];
        $methods = $class->methods();

        if ($methods->hasConstructor() && $methods->constructor()->isPublic()) {
            $constructors[] = $methods->constructor();
        }

        foreach ($methods as $method) {
            /** @infection-ignore-all */
            if (count($method->parameters()) === 0) {
                continue;
            }

            if ($method->isStatic()
                && $method->isPublic()
                && $method->returnType()->matches($class->type())
            ) {
                $constructors[] = $method;
            }
        }

        return $constructors;
    }

    /**
     * @param non-empty-list<MethodDefinition> $methods
     * @param mixed $source
     */
    private function findUsableConstructor(array $methods, $source): MethodDefinition
    {
        $constructors = [];

        foreach ($methods as $builder) {
            $filled = $this->filledParameters($builder, $source);

            if ($filled === false) {
                continue;
            }

            $constructors[$filled][] = $builder;
        }

        ksort($constructors);

        $constructorsWithMostArguments = array_pop($constructors) ?: [];

        if (count($constructorsWithMostArguments) === 0) {
            throw new CannotFindConstructor($source, $methods);
        }

        if (count($constructorsWithMostArguments) > 1) {
            throw new SeveralConstructorsFound($source, $constructorsWithMostArguments);
        }

        return $constructorsWithMostArguments[0];
    }

    /**
     * @PHP8.0 union
     *
     * @param mixed $source
     * @return bool|int<0, max>
     */
    private function filledParameters(MethodDefinition $method, $source)
    {
        $parameters = $method->parameters();

        if (! is_array($source)) {
            return count($parameters) === 1;
        }

        /** @infection-ignore-all */
        $filled = 0;

        foreach ($parameters as $parameter) {
            if (isset($source[$parameter->name()])) {
                $filled++;
            } elseif (! $parameter->isOptional()) {
                return false;
            }
        }

        /** @var int<0, max> $filled */
        return $filled;
    }
}
