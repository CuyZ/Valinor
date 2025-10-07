<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Tree\Builder;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\ArgumentsValues;
use CuyZ\Valinor\Mapper\Object\Exception\CannotFindObjectBuilder;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Tree\Message\ErrorMessage;
use CuyZ\Valinor\Mapper\Tree\Message\Message;
use CuyZ\Valinor\Mapper\Tree\Message\UserlandError;
use CuyZ\Valinor\Mapper\Tree\Shell;
use CuyZ\Valinor\Type\ObjectType;
use Throwable;

use function assert;
use function count;

/** @internal */
final class ObjectNodeBuilder implements NodeBuilder
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory,
        private InterfaceNodeBuilder $interfaceNodeBuilder,
        /** @var callable(Throwable): ErrorMessage */
        private mixed $exceptionFilter,
    ) {}

    public function build(Shell $shell): Node
    {
        assert($shell->type instanceof ObjectType);

        if ($shell->type->accepts($shell->value())) {
            return $shell->node($shell->value());
        }

        $class = $this->classDefinitionRepository->for($shell->type);

        if ($this->interfaceNodeBuilder->canInferImplementation($class)) {
            return $this->interfaceNodeBuilder->build($shell);
        }

        $objectBuilders = $this->objectBuilderFactory->for($class);

        foreach ($objectBuilders as $objectBuilder) {
            $arguments = $objectBuilder->describeArguments();
            $argumentsValues = ArgumentsValues::forClass($shell, $arguments);

            $valuesNode = $argumentsValues->shell->build();

            if (! $valuesNode->isValid()) {
                if (count($objectBuilders) > 1) {
                    continue;
                }

                return $valuesNode;
            }

            try {
                $values = $argumentsValues->transform($valuesNode->value());

                $object = $objectBuilder->buildObject($values);
            } catch (UserlandError|Message $exception) {
                if ($exception instanceof UserlandError) {
                    // @phpstan-ignore argument.type (we know there always is a previous exception)
                    $exception = ($this->exceptionFilter)($exception->getPrevious());
                }

                return $shell->error($exception);
            }

            $node = $argumentsValues->shell->node($object);

            if ($node->isValid()) {
                return $node;
            }
        }

        return $shell->error(new CannotFindObjectBuilder());
    }
}
