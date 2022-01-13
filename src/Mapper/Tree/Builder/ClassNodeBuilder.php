<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSourceForObject;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Node;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\Types\ClassType;

use function array_key_exists;
use function assert;
use function count;
use function is_array;
use function is_iterable;
use function iterator_to_array;

/** @internal */
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
        $source = $shell->value();

        assert($type instanceof ClassType);

        $class = $this->classDefinitionRepository->for($type);
        $builder = $this->objectBuilderFactory->for($class);

        $children = [];
        $arguments = [...$builder->describeArguments()];
        $source = $this->transformSource($source, ...$arguments);

        foreach ($arguments as $argument) {
            $name = $argument->name();
            $type = $argument->type();
            $attributes = $argument->attributes();
            $value = array_key_exists($name, $source) ? $source[$name] : $argument->defaultValue();

            $child = $shell->child($name, $type, $value, $attributes);
            $children[] = $rootBuilder->build($child);
        }

        $object = $this->buildObject($builder, $children);

        return Node::branch($shell, $object, $children);
    }

    /**
     * @param mixed $source
     * @return mixed[]
     */
    private function transformSource($source, Argument ...$arguments): array
    {
        if ($source === null) {
            return [];
        }

        if (is_iterable($source) && ! is_array($source)) {
            $source = iterator_to_array($source);
        }

        if (count($arguments) === 1) {
            $name = $arguments[0]->name();

            if (! is_array($source) || ! array_key_exists($name, $source)) {
                $source = [$name => $source];
            }
        }

        if (! is_array($source)) {
            throw new InvalidSourceForObject($source);
        }

        return $source;
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
