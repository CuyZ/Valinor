<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Dumper;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Tree\Builder\ObjectImplementations;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnresolvableType;
use CuyZ\Valinor\Utility\TypeHelper;

use function count;
use function usort;

/** @internal */
final class TypeDumper
{
    public function __construct(
        private readonly ClassDefinitionRepository $classDefinitionRepository,
        private readonly ObjectBuilderFactory $objectBuilderFactory,
        private ObjectImplementations $implementations,
        private FunctionsContainer $constructors,
    ) {}

    public function dump(Type $type): string
    {
        $context = $this->doDump($type, new TypeDumpContext());

        return $context->read();
    }

    private function doDump(Type $type, TypeDumpContext $context): TypeDumpContext
    {
        return match (true) {
            $type instanceof DumpableType => $this->fromDumpableType($type, $context),
            $type instanceof EnumType => $context->write($type->readableSignature()),
            $type instanceof NativeClassType => $this->fromObjectType($type, $context),
            $type instanceof InterfaceType => $this->fromInterfaceType($type, $context),
            $type instanceof UnresolvableType => $context->write('*unknown*'),
            default => $context->write($type->toString()),
        };
    }

    private function fromDumpableType(DumpableType $type, TypeDumpContext $context): TypeDumpContext
    {
        foreach ($type->dumpParts() as $item) {
            if (is_string($item)) {
                $context = $context->write($item);
            } else {
                $context = $this->doDump($item, $context);
            }
        }

        return $context;
    }

    /**
     * Loops on all constructors that can be used to instantiate a class. For
     * each one, we format its arguments to a shaped array which represents the
     * available data structure needed to construct the object.
     */
    private function fromObjectType(ObjectType $type, TypeDumpContext $context): TypeDumpContext
    {
        $class = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($class);

        usort($objectBuilders, fn ($a, $b) => $this->argumentsWeight($a->describeArguments()) <=> $this->argumentsWeight($b->describeArguments()));

        while ($builder = array_shift($objectBuilders)) {
            // @todo detect `array{…}` duplicates
            $context = $this->formatArguments($builder->describeArguments(), $context);

            if ($objectBuilders !== []) {
                $context = $context->write('|');
            }
        }

        return $context;
    }

    /**
     * Dumping an interface type works in three steps:
     *
     * 1. We check if a constructor has been registered for this interface, in
     *    which case we delegate the dump to the object dumper.
     * 2. If the interface can be inferred by using the `MapperBuilder::infer()`
     *    method, we fetch the required arguments.
     * 3. For each possible class implementation of this interface we fetch all
     *    constructors of the class, we merge their arguments with the arguments
     *    from step 2, then we transform them to a shaped array which represents
     *    the available data structure needed to construct the object.
     */
    private function fromInterfaceType(InterfaceType $type, TypeDumpContext $context): TypeDumpContext
    {
        if ($context->isTooLong()) {
            return $context->write('array{…}');
        }

        if ($this->constructorRegisteredFor($type)) {
            return $this->fromObjectType($type, $context);
        }

        if (! $this->implementations->has($type->className())) {
            return $context->write('*unknown*');
        }

        $function = $this->implementations->function($type->className());
        $interfaceArguments = Arguments::fromParameters($function->parameters);

        $classTypes = $this->implementations->implementations($type->className());

        $classesArguments = [];

        foreach ($classTypes as $classType) {
            $class = $this->classDefinitionRepository->for($classType);
            $objectBuilders = $this->objectBuilderFactory->for($class);

            foreach ($objectBuilders as $builder) {
                $arguments = $builder->describeArguments();

                // We use the arguments hash to prevent constructor duplicates
                // that can be shared between different classes.
                $classesArguments[$arguments->hash()] = $interfaceArguments->merge($arguments);
            }
        }

        usort($classesArguments, fn ($a, $b) => $this->argumentsWeight($a) <=> $this->argumentsWeight($b));

        while ($classArguments = array_shift($classesArguments)) {
            $context = $this->formatArguments($classArguments, $context);

            if ($classesArguments !== []) {
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

    private function argumentsWeight(Arguments $arguments): int
    {
        return array_reduce(
            array: $arguments->toArray(),
            callback: static fn ($weight, Argument $argument) => $weight + TypeHelper::typePriority($argument->type()),
            // @infection-ignore-all (the initial value does not really matter here)
            initial: 0,
        );
    }

    private function constructorRegisteredFor(Type $type): bool
    {
        foreach ($this->constructors as $constructor) {
            if ($type->matches($constructor->definition->returnType)) {
                return true;
            }
        }

        return false;
    }
}
