<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Resolver\Union;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderNotFound;
use CuyZ\Valinor\Type\Resolver\Exception\CannotResolveObjectTypeFromUnion;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\UnionType;

use function array_map;
use function array_pop;
use function array_shift;
use function count;
use function in_array;
use function is_array;
use function ksort;

/** @internal */
final class UnionObjectNarrower implements UnionNarrower
{
    private UnionNarrower $delegate;

    private ClassDefinitionRepository $classDefinitionRepository;

    private ObjectBuilderFactory $objectBuilderFactory;

    public function __construct(
        UnionNarrower $delegate,
        ClassDefinitionRepository $classDefinitionRepository,
        ObjectBuilderFactory $objectBuilderFactory
    ) {
        $this->delegate = $delegate;
        $this->classDefinitionRepository = $classDefinitionRepository;
        $this->objectBuilderFactory = $objectBuilderFactory;
    }

    public function narrow(UnionType $unionType, $source): Type
    {
        if (! is_array($source)) {
            return $this->delegate->narrow($unionType, $source);
        }

        $isIncremental = true;
        $types = [];
        $argumentsList = [];

        foreach ($unionType->types() as $type) {
            if (! $type instanceof ClassType) {
                return $this->delegate->narrow($unionType, $source);
            }

            $class = $this->classDefinitionRepository->for($type);

            try {
                $objectBuilder = $this->objectBuilderFactory->for($class, $source);
            } catch (ObjectBuilderNotFound $exception) { // @PHP8.0 do not capture exception
                continue;
            }

            $arguments = [...$objectBuilder->describeArguments()];

            foreach ($arguments as $argument) {
                if (! isset($source[$argument->name()]) && $argument->isRequired()) {
                    continue 2;
                }
            }

            $count = count($arguments);

            if (isset($types[$count])) {
                $isIncremental = false;
                /** @infection-ignore-all */
                break;
            }

            $types[$count] = $type;
            $argumentsList[$count] = $arguments;
        }

        ksort($types);
        ksort($argumentsList);

        if ($isIncremental && count($types) >= 1 && $this->argumentsAreSharedAcrossList($argumentsList)) {
            return array_pop($types);
        }

        throw new CannotResolveObjectTypeFromUnion($unionType);
    }

    /**
     * @param array<int, array<Argument>> $argumentsList
     */
    private function argumentsAreSharedAcrossList(array $argumentsList): bool
    {
        $namesList = [];

        foreach ($argumentsList as $arguments) {
            $namesList[] = array_map(fn (Argument $argument) => $argument->name(), $arguments);
        }

        while ($current = array_shift($namesList)) {
            if (count($namesList) === 0) {
                /** @infection-ignore-all */
                break;
            }

            foreach ($current as $name) {
                foreach ($namesList as $other) {
                    if (! in_array($name, $other, true)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }
}
