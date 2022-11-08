<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\FilledArguments;
use CuyZ\Valinor\Mapper\Object\FilteredObjectBuilder;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\UnexpectedArrayKeysForClass;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\UnionType;

use function array_filter;

/** @internal */
final class ClassNodeBuilder implements NodeBuilder
{
    private NodeBuilder $delegate;

    private ClassDefinitionRepository $classDefinitionRepository;

    private ObjectBuilderFactory $objectBuilderFactory;

    private bool $enableFlexibleCasting;

    private bool $allowSuperfluousKeys;

    public function __construct(
        NodeBuilder $delegate,
        ClassDefinitionRepository $classDefinitionRepository,
        ObjectBuilderFactory $objectBuilderFactory,
        bool $enableFlexibleCasting,
        bool $allowSuperfluousKeys
    ) {
        $this->delegate = $delegate;
        $this->classDefinitionRepository = $classDefinitionRepository;
        $this->objectBuilderFactory = $objectBuilderFactory;
        $this->enableFlexibleCasting = $enableFlexibleCasting;
        $this->allowSuperfluousKeys = $allowSuperfluousKeys;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): TreeNode
    {
        $classTypes = $this->classTypes($shell->type());

        if (empty($classTypes)) {
            return $this->delegate->build($shell, $rootBuilder);
        }

        if ($this->enableFlexibleCasting && $shell->value() === null) {
            $shell = $shell->withValue([]);
        }

        $builder = $this->builder($shell, ...$classTypes);
        $arguments = FilledArguments::forClass($builder->describeArguments(), $shell);

        $children = [];

        foreach ($arguments as $argument) {
            $name = $argument->name();
            $type = $argument->type();
            $attributes = $argument->attributes();

            $child = $shell->child($name, $type, $attributes);

            if ($arguments->has($name)) {
                $child = $child->withValue($arguments->get($name));
            }

            $children[] = $rootBuilder->build($child);
        }

        $object = $this->buildObject($builder, $children);

        $node = TreeNode::branch($shell, $object, $children);

        if (! $this->allowSuperfluousKeys) {
            $node = $this->checkForSuperfluousKeys($arguments, $node);
        }

        return $node;
    }

    /**
     * @return array<ClassType>
     */
    private function classTypes(Type $type): array
    {
        if ($type instanceof ClassType) {
            return [$type];
        }

        if ($type instanceof UnionType) {
            return array_filter($type->types(), static fn (Type $subType) => $subType instanceof ClassType);
        }

        return [];
    }

    private function builder(Shell $shell, ClassType ...$classTypes): ObjectBuilder
    {
        $builders = [];

        foreach ($classTypes as $classType) {
            $class = $this->classDefinitionRepository->for($classType);

            $builders = [...$builders, ...$this->objectBuilderFactory->for($class)];
        }

        return new FilteredObjectBuilder($shell->value(), ...$builders);
    }

    /**
     * @param TreeNode[] $children
     */
    private function buildObject(ObjectBuilder $builder, array $children): ?object
    {
        $arguments = [];

        foreach ($children as $child) {
            if (! $child->isValid()) {
                return null;
            }

            $arguments[$child->name()] = $child->value();
        }

        return $builder->build($arguments);
    }

    private function checkForSuperfluousKeys(FilledArguments $arguments, TreeNode $node): TreeNode
    {
        $superfluousKeys = $arguments->superfluousKeys();

        if (count($superfluousKeys) > 0) {
            $node = $node->withMessage(new UnexpectedArrayKeysForClass($superfluousKeys, $arguments));
        }

        return $node;
    }
}
