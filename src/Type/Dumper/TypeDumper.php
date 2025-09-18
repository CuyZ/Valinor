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

    public function dump(Type $type): string
    {
        return $this->doDump(type: $type, length: 0, weight: 0);
    }

    private function doDump(Type $type, int $length, int $weight): string
    {
        if ($type instanceof EnumType) {
            return $type->readableSignature();
        } elseif ($type instanceof ObjectType) {
            return $this->getStringTypeFromObject($type, $length, $weight);
        }

        return $type->toString();
    }

    private function getStringTypeFromObject(ObjectType $type, int $length, int $weight): string
    {
        $class = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($class);

        $textArray = array_map(
            fn (ObjectBuilder $builder) => $this->formatArguments($builder->describeArguments(), $length, $weight),
            $objectBuilders
        );

        usort($textArray, static fn ($a, $b) => $a['weight'] <=> $b['weight']);

        return implode('|', array_map(static fn ($dump) => $dump['type'], $textArray));
    }

    /**
     * @return array{weight: int, type: string}
     */
    private function formatArguments(Arguments $arguments, int $length, int $weight): array
    {
        if (count($arguments) === 1) {
            $argument = $arguments->at(0);

            return [
                'weight' => $weight + TypeHelper::typePriority($argument->type()),
                'type' => $this->doDump($argument->type(), $length, $weight),
            ];
        }

        $subTexts = [];

        foreach ($arguments as $argument) {
            $weight += TypeHelper::typePriority($argument->type());

            $subText = sprintf('%s%s: %s', $argument->name(), $argument->isRequired() ? '' : '?', $this->doDump($argument->type(), $length, $weight));

            $length += strlen($subText);

            $subTexts[] = $length > self::MAX_LENGTH ?
                sprintf('%s%s: array{…}', $argument->name(), $argument->isRequired() ? '' : '?') :
                $subText;
        }

        return [
            'weight' => $weight,
            'type' => 'array{' . implode(', ', $subTexts) . '}',
        ];
    }
}
