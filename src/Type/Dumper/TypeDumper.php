<?php

namespace CuyZ\Valinor\Type\Dumper;

use CuyZ\Valinor\Definition\Repository\ClassDefinitionRepository;
use CuyZ\Valinor\Mapper\Object\Arguments;
use CuyZ\Valinor\Mapper\Object\Factory\ObjectBuilderFactory;
use CuyZ\Valinor\Mapper\Object\ObjectBuilder;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\EnumType;
use CuyZ\Valinor\Utility\TypeHelper;

use function array_map;
use function strlen;
use function usort;
use function sprintf;
use function implode;
use function count;

/** @internal */
final class TypeDumper
{
    private const MAX_LENGTH = 150;

    public function __construct(
        private readonly ClassDefinitionRepository $classDefinitionRepository,
        private readonly ObjectBuilderFactory $objectBuilderFactory
    ) {}

    public function dump(Type $type, TypeDumpContext $context = new TypeDumpContext()): string
    {
        if ($type instanceof EnumType) {
            return $type->readableSignature();
        } elseif ($type instanceof ObjectType) {
            return $this->getStringTypeFromObject($type, $context);
        }

        return $type->toString();
    }

    private function getStringTypeFromObject(ObjectType $type, TypeDumpContext $context): string
    {
        $class = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($class);

        $textArray = array_map(
            fn (ObjectBuilder $builder) => $this->formatArguments($builder->describeArguments(), $context),
            $objectBuilders
        );

        usort($textArray, fn (ArgumentsDump $a, ArgumentsDump $b) => $a->weight <=> $b->weight);
        return implode('|', array_map(fn (ArgumentsDump $dump) => $dump->type, $textArray));
    }

    private function formatArguments(Arguments $arguments, TypeDumpContext $context): ArgumentsDump
    {
        $argumentsArray = $arguments->toArray();

        if (count($argumentsArray) === 1) {
            $arg = reset($argumentsArray);
            $context = $context->addWeight(TypeHelper::typePriority($arg->type()));
            return new ArgumentsDump($context->weight, $this->dump($arg->type(), $context));
        }

        $subTexts = [];
        foreach ($argumentsArray as $arg) {
            $context = $context->addWeight(TypeHelper::typePriority($arg->type()));
            $subText = sprintf('%s%s: %s', $arg->name(), $arg->isRequired() ? '' : '?', $this->dump($arg->type(), $context));
            $context = $context->addLength(strlen($subText));
            $subTexts[] = $context->length > self::MAX_LENGTH ?
                sprintf('%s%s: array{…}', $arg->name(), $arg->isRequired() ? '' : '?') :
                $subText;
        }

        return new ArgumentsDump($context->weight, 'array{' . implode(', ', $subTexts) . '}');
    }

}
