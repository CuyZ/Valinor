<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Mapper\Tree\Exception\CircularDependencyDetected;

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
                    if ($argument->type()->toString() === $class->type->toString()) {
                        throw new CircularDependencyDetected($argument);
                    }
                }
            }
        }

        return $builders;
    }
}
