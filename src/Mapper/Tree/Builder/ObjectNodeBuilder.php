<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Object\Exception\CannotFindObjectBuilder;
use CuyZ\Valinor\Mapper\Object\Exception\InvalidSource;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Exception\CircularDependencyDetected;
use CuyZ\Valinor\Mapper\Tree\Exception\InvalidNodeValue;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use Throwable;

use function array_keys;
use function assert;
use function count;

/** @internal */
final class ObjectNodeBuilder implements NodeBuilder
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function build(Shell $shell, RootNodeBuilder $rootBuilder): Node
    {
        $type = $shell->type();

        // @infection-ignore-all
        assert($type instanceof ObjectType);

        if ($type->accepts($shell->value())) {
            return Node::new($shell->value());
        }

        if ($shell->allowUndefinedValues() && $shell->value() === null) {
            $shell = $shell->withValue([]);
        } else {
            $shell = $shell->transformIteratorToArray();
        }

        $class = $this->classDefinitionRepository->for($type);
        $builders = $this->objectBuilderFactory->for($class);

        foreach ($builders as $builder) {
            $argumentsValues = ArgumentsValues::forClass($builder->describeArguments(), $shell);

            if ($argumentsValues->hasInvalidValue()) {
                if (count($builders) === 1) {
                    return Node::error($shell, new InvalidSource($shell->value(), $builder->describeArguments()));
                }

                continue;
            }

            $children = $this->children($shell, $argumentsValues, $rootBuilder);

            try {
                $object = $this->buildObject($builder, $children);
            } catch (UserlandError|Message $exception) {
                if ($exception instanceof UserlandError) {
                    // @phpstan-ignore argument.type (we know there always is a previous exception)
                    $exception = ($this->exceptionFilter)($exception->getPrevious());
                }

                return Node::error($shell, $exception);
            }

            if ($object === null) {
                if (count($builders) > 1) {
                    continue;
                }

                $node = Node::branchWithErrors($children);
            } else {
                $node = Node::new(value: $object, childrenCount: count($children));
            }

            if (! $argumentsValues->hadSingleArgument()) {
                $node = $node->checkUnexpectedKeys($shell, array_keys($children));
            }

            if ($node->isValid() || count($builders) === 1) {
                return $node;
            }
        }

        return Node::error($shell, new CannotFindObjectBuilder($builders));
    }

    /**
     * @return array<non-empty-string, Node>
     */
    private function children(Shell $shell, ArgumentsValues $arguments, RootNodeBuilder $rootBuilder): array
    {
        $children = [];

        foreach ($arguments as $argument) {
            $name = $argument->name();
            $type = $argument->type();

            if ($arguments->hadSingleArgument()) {
                $child = $shell->withType($type);
            } else {
                $child = $shell->child($name, $type);
            }

            $child = $child->withAttributes($argument->attributes());

            if ($arguments->hasValue($name)) {
                $child = $child->withValue($arguments->getValue($name));
            }

            // This whole block is needed to detect object circular dependencies
            // and prevent infinite loops.
            if ($rootBuilder->typeWasSeen($type)) {
                // An exception is thrown only when the type of the property is
                // literally the same as the type of the object being built.
                // Otherwise, the property type might be a union, for instance,
                // so we do not want to stop the script execution right away
                // because the value might be valid.
                if (count($arguments) === 1 && $type instanceof ObjectType) {
                    throw new CircularDependencyDetected($argument);
                }

                $children[$name] = Node::error($shell, new InvalidNodeValue($type));
            } else {
                $childBuilder = $rootBuilder;

                if ($type->matches($shell->type())) {
                    $childBuilder = $rootBuilder->withTypeAsCurrentRoot($type);
                }

                $children[$name] = $childBuilder->build($child);
            }
        }

        return $children;
    }

    /**
     * @param array<non-empty-string, Node> $children
     */
    private function buildObject(ObjectBuilder $builder, array $children): ?object
    {
        $arguments = [];

        foreach ($children as $name => $child) {
            if (! $child->isValid()) {
                return null;
            }

            $arguments[$name] = $child->value();
        }

        return $builder->build($arguments);
    }
}
