<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\MethodDefinition;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilderFilterer;
use CuyZ\Valinor\Mapper\Object\ReflectionObjectBuilder;

use function array_map;
use function count;

/** @internal */
final class ConstructorObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFilterer $objectBuilderFilterer;

    public function __construct(ObjectBuilderFilterer $objectBuilderFilterer)
    {
        $this->objectBuilderFilterer = $objectBuilderFilterer;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        $constructors = $this->findConstructors($class);

        if (count($constructors) === 0) {
            return new ReflectionObjectBuilder($class);
        }

        $builders = array_map(
            fn (MethodDefinition $constructor) => new MethodObjectBuilder($class, $constructor->name()),
            $constructors
        );

        return $this->objectBuilderFilterer->filter($source, ...$builders);
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
}
