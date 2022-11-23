<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilteredObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ClassType;

use function assert;

/** @internal */
final class ProxyClassNodeBuilder implements NodeBuilder
{
    private ClassDefinitionRepository $classDefinitionRepository;

    private ObjectBuilderFactory $objectBuilderFactory;

    private ClassNodeBuilder $classBuilder;

    private bool $enableFlexibleCasting;

    public function __construct(
        ClassDefinitionRepository $classDefinitionRepository,
        ObjectBuilderFactory $objectBuilderFactory,
        ClassNodeBuilder $classBuilder,
        bool $enableFlexibleCasting
    ) {
        $this->classDefinitionRepository = $classDefinitionRepository;
        $this->objectBuilderFactory = $objectBuilderFactory;
        $this->classBuilder = $classBuilder;
        $this->enableFlexibleCasting = $enableFlexibleCasting;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        // @infection-ignore-all
        assert($type instanceof ClassType);

        if ($this->enableFlexibleCasting && $shell->value() === null) {
            $shell = $shell->withValue([]);
        }

        $class = $this->classDefinitionRepository->for($type);
        $objectBuilder = new FilteredObjectBuilder($shell->value(), ...$this->objectBuilderFactory->for($class));

        return $this->classBuilder->build($objectBuilder, $shell, $rootBuilder);
    }
}
