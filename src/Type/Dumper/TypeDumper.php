<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Dumper;

use CuyZ\Valinor\Definition\FunctionsContainer;
use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Mapper\Tree\Builder\InterfaceInferringContainer;
use CuyZ\Valinor\Mapper\Tree\Exception\ObjectImplementationCallbackError;
use CuyZ\Valinor\Type\DumpableType;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\VacantType;
use CuyZ\Valinor\Utility\TypeHelper;

use function array_map;
use function array_reduce;
use function array_shift;
use function count;
use function hash;
use function is_string;
use function ksort;
use function serialize;
use function sprintf;
use function usort;

/** @internal */
final class TypeDumper
{
    /** @var array<string, string> */
    private array $dumpedTypes = [];

    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory,
        private InterfaceInferringContainer $interfaceInferringContainer,
        private FunctionsContainer $constructors,
    ) {}

    public function dump(Type $type): string
    {
        return $this->dumpedTypes[$type->toString()] ??= $this->dumpAndQuote($type);
    }

    private function dumpAndQuote(Type $type): string
    {
        $context = $this->doDump($type, new TypeDumpContext());

        if ($type instanceof FixedType || $type instanceof EnumType || $type instanceof VacantType) {
            return $context->read();
        }

        return '`' . $context->read() . '`';
    }

    private function doDump(Type $type, TypeDumpContext $context): TypeDumpContext
    {
        return match (true) {
            $type instanceof DumpableType => $this->fromDumpableType($type, $context),
            $type instanceof EnumType => $context->write($type->readableSignature()),
            $type instanceof NativeClassType => $this->fromObjectType($type, $context),
            $type instanceof InterfaceType => $this->fromInterfaceType($type, $context),
            $type instanceof VacantType => $context->write('*unknown*'),
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
        if ($context->typeIsRetained($type)) {
            return $context->write('array{…}');
        }

        $context = $context->retain($type);

        $class = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($class);

        usort($objectBuilders, fn ($a, $b) => $this->argumentsWeight($a->describeArguments()) <=> $this->argumentsWeight($b->describeArguments()));

        $arguments = array_map(
            static fn (ObjectBuilder $builder) => $builder->describeArguments(),
            $objectBuilders,
        );

        $context = $this->formatArguments($arguments, $context);
        $context = $context->forgetLastRetained();

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

        if (! $this->interfaceInferringContainer->has($type->className())) {
            return $context->write('*unknown*');
        }

        $function = $this->interfaceInferringContainer->inferFunctionFor($type->className());
        $interfaceArguments = Arguments::fromParameters($function->parameters);

        try {
            $classTypes = $this->interfaceInferringContainer->classImplementationsFor($type->className());
        } catch (ObjectImplementationCallbackError) {
            return $context->write('*unknown*');
        }

        $classArguments = [];

        foreach ($classTypes as $classType) {
            $class = $this->classDefinitionRepository->for($classType);
            $objectBuilders = $this->objectBuilderFactory->for($class);

            foreach ($objectBuilders as $builder) {
                $arguments = $builder->describeArguments();

                // We use the arguments hash to prevent constructor duplicates
                // that can be shared between different classes.
                $classArguments[$this->argumentsHash($arguments)] = $interfaceArguments->merge($arguments);
            }
        }

        usort($classArguments, fn ($a, $b) => $this->argumentsWeight($a) <=> $this->argumentsWeight($b));

        return $this->formatArguments($classArguments, $context);
    }

    /**
     * @param non-empty-array<Arguments> $argumentsList
     */
    private function formatArguments(array $argumentsList, TypeDumpContext $context): TypeDumpContext
    {
        while ($arguments = array_shift($argumentsList)) {
            if (count($arguments) === 1) {
                $context = $this->doDump($arguments->at(0)->type(), $context);
                $context = $context->write('|');
            }

            if ($context->isTooLong()) {
                return $context->write('array{…}');
            } else {
                $arguments = $arguments->toArray();
                $context = $context->write('array{');

                while ($argument = array_shift($arguments)) {
                    $context = $context->write(sprintf('%s%s: ', $argument->name(), $argument->isRequired() ? '' : '?'));
                    $context = $this->doDump($argument->type(), $context);

                    if ($arguments !== []) {
                        $context = $context->write(', ');
                    }
                }

                $context = $context->write('}');
            }

            if ($argumentsList !== []) {
                $context = $context->write('|');
            }
        }

        return $context;
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

    private function argumentsHash(Arguments $arguments): string
    {
        $arguments = $arguments->toArray();

        ksort($arguments);

        $arguments = array_map(static fn (Argument $argument) => $argument->type()->toString(), $arguments);

        return hash('xxh128', serialize($arguments));
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
