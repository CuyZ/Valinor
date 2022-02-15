<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Object\Factory;

use CuyZ\Valinor\Definition\ClassDefinition;
use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\CallbackObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilderFilterer;

/** @internal */
final class ObjectBindingBuilderFactory implements ObjectBuilderFactory
{
    private ObjectBuilderFactory $delegate;

    private FunctionDefinitionRepository $functionDefinitionRepository;

    private ObjectBuilderFilterer $objectBuilderFilterer;

    /** @var list<callable> */
    private array $callbacks;

    /** @var list<FunctionDefinition> */
    private array $functions;

    /**
     * @param list<callable> $callbacks
     */
    public function __construct(
        ObjectBuilderFactory $delegate,
        FunctionDefinitionRepository $functionDefinitionRepository,
        ObjectBuilderFilterer $objectBuilderFilterer,
        array $callbacks
    ) {
        $this->delegate = $delegate;
        $this->functionDefinitionRepository = $functionDefinitionRepository;
        $this->objectBuilderFilterer = $objectBuilderFilterer;
        $this->callbacks = $callbacks;
    }

    public function for(ClassDefinition $class, $source): ObjectBuilder
    {
        $builders = [];

        foreach ($this->functions() as $key => $function) {
            if ($function->returnType()->matches($class->type())) {
                $builders[] = new CallbackObjectBuilder($function, $this->callbacks[$key]);
            }
        }

        if (empty($builders)) {
            return $this->delegate->for($class, $source);
        }

        return $this->objectBuilderFilterer->filter($source, ...$builders);
    }

    /**
     * @return FunctionDefinition[]
     */
    private function functions(): array
    {
        if (! isset($this->functions)) {
            $this->functions = [];

            foreach ($this->callbacks as $key => $callback) {
                $this->functions[$key] = $this->functionDefinitionRepository->for($callback);
            }
        }

        return $this->functions;
    }
}
