<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Dumper;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Utility\TypeHelper;

use function count;
use function usort;

/** @internal */
final class TypeDumper
{
    public function __construct(
        private readonly ClassDefinitionRepository $classDefinitionRepository,
        private readonly ObjectBuilderFactory $objectBuilderFactory,
    ) {}

    public function dump(Type $type): string
    {
        $context = $this->doDump($type, new TypeDumpContext());

        return $context->read();
    }

    private function doDump(Type $type, TypeDumpContext $context): TypeDumpContext
    {
        $context = $context->increaseDepth();

        $context = match (true) {
            $type instanceof EnumType => $context->write($type->readableSignature()),
            $type instanceof NativeClassType => $this->getStringTypeFromObject($type, $context),
            $type instanceof InterfaceType => $context->write('todo interface'),
            default => $context->write($type->toString()),
        };

        return $context->decreaseDepth();
    }

    private function getStringTypeFromObject(ObjectType $type, TypeDumpContext $context): TypeDumpContext
    {
        $class = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($class);

        usort($objectBuilders, fn ($a, $b) => $this->objectBuilderWeight($a) <=> $this->objectBuilderWeight($b));

        while ($builder = array_shift($objectBuilders)) {
            // @todo detect `array{…}` duplicates
            $context = $this->formatArguments($builder->describeArguments(), $context);

            if ($objectBuilders !== []) {
                $context = $context->write('|');
            }
        }

        return $context;
    }

    private function formatArguments(Arguments $arguments, TypeDumpContext $context): TypeDumpContext
    {
        if (count($arguments) === 1) {
            return $this->doDump($arguments->at(0)->type(), $context);
        }

        if ($context->isTooLong()) {
            return $context->write('array{…}');
        }

        $arguments = $arguments->toArray();
        $context = $context->write('array{');

        while ($argument = array_shift($arguments)) {
            $context = $context->write(sprintf('%s%s: ', $argument->name(), $argument->isRequired() ? '' : '?'));
            $context = $this->doDump($argument->type(), $context);

            if ($arguments !== []) {
                $context = $context->write(', ');
            }
        }

        return $context->write('}');
    }

    private function objectBuilderWeight(ObjectBuilder $builder): int
    {
        return array_reduce(
            $builder->describeArguments()->toArray(),
            static fn ($weight, Argument $argument) => $weight + TypeHelper::typePriority($argument->type()),
            0,
        );
    }
}
