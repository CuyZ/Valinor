<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\FunctionDefinition;
use CuyZ\Valinor\Definition\Repository\FunctionDefinitionRepository;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;

/** @internal */
final class ValueAlteringNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private FunctionDefinitionRepository $functionDefinitionRepository;

    /** @var list<callable> */
    private array $callbacks;

    /** @var list<FunctionDefinition> */
    private array $functions;

    /**
     * @param list<callable> $callbacks
     */
    public function __construct(
        NodeBuilder $delegate,
        FunctionDefinitionRepository $functionDefinitionRepository,
        array $callbacks
    ) {
        $this->delegate = $delegate;
        $this->functionDefinitionRepository = $functionDefinitionRepository;
        $this->callbacks = $callbacks;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $node = $this->delegate->build($shell, $rootBuilder);

        if (! $node->isValid()) {
            return $node;
        }

        $value = $node->value();
        $type = $node->type();

        foreach ($this->functions() as $key => $function) {
            $parameters = $function->parameters();

            if (count($parameters) === 0) {
                continue;
            }

            if ($parameters->at(0)->type()->matches($type)) {
                $value = ($this->callbacks[$key])($value);
                $node = $node->withValue($value);
            }
        }

        return $node;
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
