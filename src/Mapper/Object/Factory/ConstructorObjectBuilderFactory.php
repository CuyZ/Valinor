<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\Exception\CannotInstantiateObject;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidClassConstructorType;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\MethodObjectBuilder;
use CuyZ\Valinor\Mapper\Object\NativeConstructorObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;

use function array_key_exists;
use function array_unshift;
use function count;

/** @internal */
final class ConstructorObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    /** @var array<class-string, null> */
    public array $nativeConstructors = [];

    private FunctionsContainer $constructors;

    /** @var array<string, ObjectBuilder[]> */
    private array $builders = [];

    /**
     * @param array<class-string, null> $nativeConstructors
     */
    public function __construct(
        ObjectBuilderFactory $delegate,
        array $nativeConstructors,
        FunctionsContainer $constructors
    ) {
        $this->delegate = $delegate;
        $this->nativeConstructors = $nativeConstructors;
        $this->constructors = $constructors;
    }

    public function for(ClassDefinition $class): array
    {
        $builders = $this->listBuilders($class);

        if (count($builders) === 0) {
            if ($class->methods()->hasConstructor()) {
                throw new CannotInstantiateObject($class);
            }

            return $this->delegate->for($class);
        }

        return $builders;
    }

    /**
     * @return list<ObjectBuilder>
     */
    private function listBuilders(ClassDefinition $class): array
    {
        $type = $class->type();
        $key = $type->toString();

        if (! array_key_exists($key, $this->builders)) {
            $builders = [];

            $className = $class->name();
            $methods = $class->methods();

            foreach ($this->constructors as $constructor) {
                $definition = $constructor->definition();
                $handledType = $definition->returnType();
                $functionClass = $definition->class();

                if (! $handledType instanceof ClassType && ! $handledType instanceof InterfaceType) {
                    throw new InvalidClassConstructorType($constructor->definition(), $handledType);
                }

                if (! $handledType->matches($type)) {
                    continue;
                }

                if ($functionClass && $definition->isStatic()) {
                    $builders[] = new MethodObjectBuilder($className, $definition->name(), $definition->parameters());
                } else {
                    $builders[] = new FunctionObjectBuilder($constructor);
                }
            }

            if ((array_key_exists($className, $this->nativeConstructors) || count($builders) === 0)
                && $methods->hasConstructor()
                && $methods->constructor()->isPublic()
            ) {
                array_unshift($builders, new NativeConstructorObjectBuilder($class));
            }

            $this->builders[$key] = $builders;
        }

        return $this->builders[$key];
    }
}
