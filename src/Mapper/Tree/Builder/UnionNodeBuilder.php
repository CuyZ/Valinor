<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilteredObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveTypeFromUnion;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\EnumType;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\UnionType;

use function count;

/** @internal */
final class UnionNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private ClassDefinitionRepository $classDefinitionRepository;

    private ObjectBuilderFactory $objectBuilderFactory;

    private ClassNodeBuilder $classNodeBuilder;

    private bool $enableFlexibleCasting;

    public function __construct(
        NodeBuilder $delegate,
        ClassDefinitionRepository $classDefinitionRepository,
        ObjectBuilderFactory $objectBuilderFactory,
        ClassNodeBuilder $classNodeBuilder,
        bool $enableFlexibleCasting
    ) {
        $this->delegate = $delegate;
        $this->classDefinitionRepository = $classDefinitionRepository;
        $this->objectBuilderFactory = $objectBuilderFactory;
        $this->classNodeBuilder = $classNodeBuilder;
        $this->enableFlexibleCasting = $enableFlexibleCasting;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $type = $shell->type();

        if (! $type instanceof UnionType) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        $classNode = $this->tryToBuildClassNode($type, $shell, $rootBuilder);

        if ($classNode instanceof TreeNode) {
            return $classNode;
        }

        $narrowedType = $this->narrow($type, $shell->value());

        return $rootBuilder->build($shell->withType($narrowedType));
    }

    /**
     * @param mixed $source
     */
    private function narrow(UnionType $type, $source): Type
    {
        $subTypes = $type->types();

        if ($source !== null && count($subTypes) === 2) {
            if ($subTypes[0] instanceof NullType) {
                return $subTypes[1];
            } elseif ($subTypes[1] instanceof NullType) {
                return $subTypes[0];
            }
        }

        foreach ($subTypes as $subType) {
            if (! $subType instanceof ScalarType) {
                continue;
            }

            if (! $this->enableFlexibleCasting && ! $subType instanceof EnumType) {
                continue;
            }

            if ($subType->canCast($source)) {
                return $subType;
            }
        }

        throw new CannotResolveTypeFromUnion($source, $type);
    }

    private function tryToBuildClassNode(UnionType $type, Shell $shell, RootNodeBuilder $rootBuilder): ?TreeNode
    {
        $classTypes = [];

        foreach ($type->types() as $subType) {
            if (! $subType instanceof ClassType) {
                return null;
            }

            $classTypes[] = $subType;
        }

        $objectBuilder = $this->objectBuilder($shell->value(), ...$classTypes);

        return $this->classNodeBuilder->build($objectBuilder, $shell, $rootBuilder);
    }

    /**
     * @param mixed $value
     */
    private function objectBuilder($value, ClassType ...$types): ObjectBuilder
    {
        $builders = [];

        foreach ($types as $type) {
            $class = $this->classDefinitionRepository->for($type);

            $builders = [...$builders, ...$this->objectBuilderFactory->for($class)];
        }

        return new FilteredObjectBuilder($value, ...$builders);
    }
}
