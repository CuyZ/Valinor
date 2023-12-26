<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilteredObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\CannotResolveTypeFromUnion;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ScalarType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Types\NullType;
use CuyZ\Valinor\Type\Types\UnionType;

use function count;

/** @internal */
final class UnionNodeBuilder implements NodeBuilder
{
    public function __construct(
        private NodeBuilder $delegate,
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory,
        private ObjectNodeBuilder $objectNodeBuilder,
        private bool $enableFlexibleCasting
    ) {}

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

    private function narrow(UnionType $type, mixed $source): Type
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

            if (! $this->enableFlexibleCasting) {
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
        $classTypes = array_filter(
            $type->types(),
            fn (Type $type) => $type instanceof ClassType,
        );

        if (count($classTypes) === 0) {
            return null;
        }

        $objectBuilder = $this->objectBuilder($shell->value(), ...$classTypes);

        return $this->objectNodeBuilder->build($objectBuilder, $shell, $rootBuilder);
    }

    private function objectBuilder(mixed $value, ClassType ...$types): ObjectBuilder
    {
        $builders = [];

        foreach ($types as $type) {
            $class = $this->classDefinitionRepository->for($type);

            $builders = [...$builders, ...$this->objectBuilderFactory->for($class)];
        }

        return new FilteredObjectBuilder($value, ...$builders);
    }
}
