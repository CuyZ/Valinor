<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ClassType;

use function assert;

final class ClassNodeBuilder implements NodeBuilder
{
    private ClassDefinitionRepository $classDefinitionRepository;

    private ObjectBuilderFactory $objectBuilderFactory;

    public function __construct(ClassDefinitionRepository $classDefinitionRepository, ObjectBuilderFactory $objectBuilderFactory)
    {
        $this->classDefinitionRepository = $classDefinitionRepository;
        $this->objectBuilderFactory = $objectBuilderFactory;
    }

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();
        $value = $shell->value();

        assert($type instanceof ClassType);

        $class = $this->classDefinitionRepository->for($type->signature());
        $builder = $this->objectBuilderFactory->for($class);

        $children = [];

        foreach ($builder->describeArguments($value) as $arg) {
            $child = $shell->child($arg->name(), $arg->type(), $arg->value(), $arg->attributes());

            $children[] = $rootBuilder->build($child);
        }

        $object = $this->buildObject($builder, $children);

        return Node::branch($shell, $object, $children);
    }

    /**
     * @param Node[] $children
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
}
