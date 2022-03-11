<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\DateTimeObjectBuilder;
use CuyZ\Valinor\Mapper\Object\FunctionObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilderFilterer;
use DateTimeInterface;

use function is_a;

/** @internal */
final class DateTimeObjectBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    private FunctionsContainer $functions;

    private ObjectBuilderFilterer $objectBuilderFilterer;

    /** @var array<string, ObjectBuilder[]> */
    private array $builders = [];

    public function __construct(
        ObjectBuilderFactory $delegate,
        FunctionsContainer $functions,
        ObjectBuilderFilterer $objectBuilderFilterer
    ) {
        $this->delegate = $delegate;
        $this->functions = $functions;
        $this->objectBuilderFilterer = $objectBuilderFilterer;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        if (! is_a($class->name(), DateTimeInterface::class, true)) {
            return $this->delegate->for($class, $source);
        }

        $builders = $this->builders($class);

        if (count($builders) === 0) {
            return new DateTimeObjectBuilder($class->name());
        }

        try {
            return $this->objectBuilderFilterer->filter($source, ...$builders);
        } catch (SuitableObjectBuilderNotFound $exception) {
            // @PHP8.0 remove variable
            return new DateTimeObjectBuilder($class->name());
        }
    }

    /**
     * @return list<ObjectBuilder>
     */
    private function builders(ClassDefinition $class): array
    {
        $type = $class->type();
        $key = $type->__toString();

        if (! isset($this->builders[$key])) {
            $this->builders[$key] = [];

            foreach ($this->functions as $function) {
                if (! $function->returnType()->matches($type)) {
                    continue;
                }

                $this->builders[$key][] = new FunctionObjectBuilder($function, $this->functions->callback($function));
            }
        }

        return $this->builders[$key];
    }
}
