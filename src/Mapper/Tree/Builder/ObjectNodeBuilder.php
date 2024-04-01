<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilteredObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Shell;

use CuyZ\Valinor\Type\ObjectType;

use function assert;

/** @internal */
final class ObjectNodeBuilder implements NodeBuilder
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory,
        private FilteredObjectNodeBuilder $filteredObjectNodeBuilder,
        private bool $enableFlexibleCasting,
    ) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        // @infection-ignore-all
        assert($type instanceof ObjectType);

        if ($this->enableFlexibleCasting && $shell->value() === null) {
            $shell = $shell->withValue([]);
        }

        $class = $this->classDefinitionRepository->for($type);
        $objectBuilder = FilteredObjectBuilder::from($shell->value(), ...$this->objectBuilderFactory->for($class));

        return $this->filteredObjectNodeBuilder->build($objectBuilder, $shell, $rootBuilder);
    }
}
