<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\Types\ClassType;
use DateTimeInterface;

use function count;
use function is_a;

/** @internal */
final class DateTimeObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    private FunctionsContainer $functions;

    /** @var array<string, ObjectBuilder[]> */
    private array $builders = [];

    public function __construct(ObjectBuilderFactory $delegate, FunctionsContainer $functions)
    {
        $this->delegate = $delegate;
        $this->functions = $functions;
    }

    public function for(ClassDefinition $class): array
    {
        $className = $class->name();

        if (! is_a($className, DateTimeInterface::class, true)) {
            return $this->delegate->for($class);
        }

        return $this->builders($class->type());
    }

    /**
     * @return list<ObjectBuilder>
     */
    private function builders(ClassType $type): array
    {
        /** @var class-string<DateTimeInterface> $className */
        $className = $type->className();
        $key = $type->toString();

        if (! isset($this->builders[$key])) {
            $overridesDefault = false;

            $this->builders[$key] = [];

            foreach ($this->functions as $function) {
                $definition = $function->definition();

                if (! $definition->returnType()->matches($type)) {
                    continue;
                }

                if (count($definition->parameters()) === 1) {
                    $overridesDefault = true;
                }

                $this->builders[$key][] = new FunctionObjectBuilder($function, $type);
            }

            if (! $overridesDefault) {
                $this->builders[$key][] = new DateTimeObjectBuilder($className);
            }
        }

        return $this->builders[$key];
    }
}
