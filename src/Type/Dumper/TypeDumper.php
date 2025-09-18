<?php

declare(strict_types=1);

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
use function count;
use function implode;
use function sprintf;
use function strlen;
use function usort;

/** @internal */
final class TypeDumper
{
    private const MAX_LENGTH = 150;

    public function __construct(
        private readonly ClassDefinitionRepository $classDefinitionRepository,
        private readonly ObjectBuilderFactory $objectBuilderFactory,
    ) {}

    public function dump(Type $type): string
    {
        $dump = $this->doDump(type: $type, weight: 0, truncate: false);

        // If the dump is too long, we re-dump everything but the sub-objects
        // are being truncated to lower the dump length.
        if (strlen($dump) > self::MAX_LENGTH) {
            return $this->doDump(type: $type, weight: 0, truncate: true);
        }

        return $dump;
    }

    private function doDump(Type $type, int $weight, bool $truncate): string
    {
        return match (true) {
            $type instanceof EnumType => $type->readableSignature(),
            $type instanceof ObjectType => $this->getStringTypeFromObject($type, $weight, $truncate),
            default => $type->toString(),
        };
    }

    private function getStringTypeFromObject(ObjectType $type, int $weight, bool $truncate): string
    {
        if ($truncate && $weight > 0) {
            return 'array{…}';
        }

        $class = $this->classDefinitionRepository->for($type);
        $objectBuilders = $this->objectBuilderFactory->for($class);

        $textArray = array_map(
            fn (ObjectBuilder $builder) => $this->formatArguments($builder->describeArguments(), $weight, $truncate),
            $objectBuilders,
        );

        usort($textArray, static fn ($a, $b) => $a['weight'] <=> $b['weight']);

        return implode('|', array_map(static fn ($dump) => $dump['type'], $textArray));
    }

    /**
     * @return array{type: string, weight: int}
     */
    private function formatArguments(Arguments $arguments, int $weight, bool $truncate): array
    {
        if (count($arguments) === 1) {
            $argument = $arguments->at(0);

            return [
                'type' => $this->doDump($argument->type(), $weight, $truncate),
                'weight' => $weight + TypeHelper::typePriority($argument->type()),
            ];
        }

        $subTexts = [];

        foreach ($arguments as $argument) {
            $weight += TypeHelper::typePriority($argument->type());

            $subTexts[] = sprintf('%s%s: %s', $argument->name(), $argument->isRequired() ? '' : '?', $this->doDump($argument->type(), $weight, $truncate));
        }

        return [
            'type' => 'array{' . implode(', ', $subTexts) . '}',
            'weight' => $weight,
        ];
    }
}
