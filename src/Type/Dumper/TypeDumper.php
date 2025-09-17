<?php

namespace CuyZ\Valinor\Type\Dumper;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Argument;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\FixedType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;

/** @internal */
final class TypeDumper
{
    public function __construct(
        private ClassDefinitionRepository $classDefinitionRepository,
        private ObjectBuilderFactory $objectBuilderFactory
    ) {}

    public function dump(Type $type): string
    {
        if ($type instanceof EnumType) {
            $text = $type->readableSignature();
        } elseif ($type instanceof FixedType) {
            return $type->toString();
        } elseif ($type instanceof ObjectType) {
            $text = $this->getStringTypeFromObject($type);
        } else {
            $text = $type->toString();
        }

        return $text;
    }

    private function getStringTypeFromObject(ObjectType $type): string
    {
        $class = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($class);

        $textArray = array_map(
            fn (ObjectBuilder $builder) => $this->formatArguments($builder->describeArguments()),
            $objectBuilders
        );

        return implode('|', $textArray);
    }

    private function formatArguments(Arguments $arguments): string
    {
        $argumentsArray = $arguments->toArray();

        if (count($argumentsArray) === 1) {
            $argument = reset($argumentsArray);
            return $this->dump($argument->type());
        }

        $subTexts = array_map(
            fn (Argument $arg) => sprintf('%s: %s', $arg->name(), $this->dump($arg->type())),
            $argumentsArray
        );

        return 'array{' . implode(', ', $subTexts) . '}';
    }
}
