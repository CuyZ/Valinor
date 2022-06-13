<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\Type;
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

    public function for(ClassDefinition $class): iterable
    {
        $className = $class->name();

        if (! is_a($className, DateTimeInterface::class, true)) {
            return $this->delegate->for($class);
        }

        return $this->builders($class->type(), $className);
    }

    /**
     * @param class-string<DateTimeInterface> $className
     * @return list<ObjectBuilder>
     */
    private function builders(Type $type, string $className): array
    {
        $key = $type->__toString();

        if (! isset($this->builders[$key])) {
            $overridesDefault = false;

            $this->builders[$key] = [];

            foreach ($this->functions as $function) {
                if (! $function->returnType()->matches($type)) {
                    continue;
                }

                if (count($function->parameters()) === 1) {
                    $overridesDefault = true;
                }

                $this->builders[$key][] = new FunctionObjectBuilder($function, $this->functions->callback($function));
            }

            if (! $overridesDefault) {
                $this->builders[$key][] = new DateTimeObjectBuilder($className);
            }
        }

        return $this->builders[$key];
    }
}
