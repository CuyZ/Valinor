<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Tree\Exception\CircularDependencyDetected;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\ObjectWithGenericType;
use CuyZ\Valinor\Type\Type;

use function array_map;
use function array_slice;
use function count;

/** @internal */
final class CircularDependencyDetectorObjectBuilderFactory implements ObjectBuilderFactory
{
    /** @var array<class-string, null> */
    private array $circularDependencyChecked = [];

    public function __construct(private ObjectBuilderFactory $delegate) {}

    public function for(ClassDefinition $class): array
    {
        $builders = $this->delegate->for($class);

        if (! isset($this->circularDependencyChecked[$class->name])) {
            $this->circularDependencyChecked[$class->name] = null;

            foreach ($builders as $builder) {
                foreach ($builder->describeArguments() as $argument) {
                    if ($this->isSameType($argument->type(), $class->type)) {
                        throw new CircularDependencyDetected($argument);
                    }
                }
            }
        }

        return $builders;
    }

    private function isSameType(Type $argumentType, ObjectType $classType): bool
    {
        if (! $argumentType instanceof ObjectWithGenericType || ! $classType instanceof ObjectWithGenericType) {
            return $argumentType->toString() === $classType->toString();
        }

        if ($argumentType->className() !== $classType->className()) {
            return false;
        }

        $toString = static fn (Type $type) => $type->toString();

        $argumentGenerics = array_map($toString, $argumentType->generics());
        $classGenerics = array_map($toString, $classType->generics());

        return $argumentGenerics === array_slice($classGenerics, 0, count($argumentGenerics));
    }
}
