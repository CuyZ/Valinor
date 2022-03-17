<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Mapper\Object\CallbackObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilderFilterer;

/** @internal */
final class ObjectBindingBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    private ObjectBuilderFilterer $objectBuilderFilterer;

    private FunctionsContainer $functions;

    public function __construct(
        ObjectBuilderFactory $delegate,
        ObjectBuilderFilterer $objectBuilderFilterer,
        FunctionsContainer $functions
    ) {
        $this->delegate = $delegate;
        $this->objectBuilderFilterer = $objectBuilderFilterer;
        $this->functions = $functions;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        $builders = [];

        foreach ($this->functions as $function) {
            if ($function->returnType()->matches($class->type())) {
                $builders[] = new CallbackObjectBuilder($function, $this->functions->callback($function));
            }
        }

        if (empty($builders)) {
            return $this->delegate->for($class, $source);
        }

        return $this->objectBuilderFilterer->filter($source, ...$builders);
    }
}
