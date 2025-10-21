<?php

declare(strict_types=1);

namespace CuyZ\Valinor\QA\Psalm\Plugin;

use CuyZ\Valinor\Mapper\TreeMapper;
use Psalm\Plugin\EventHandler\Event\MethodReturnTypeProviderEvent;
use Psalm\Plugin\EventHandler\MethodReturnTypeProviderInterface;
use Psalm\Type;
use Psalm\Type\Atomic;
use Psalm\Type\Atomic\TClassString;
use Psalm\Type\Atomic\TDependentGetClass;
use Psalm\Type\Atomic\TLiteralString;
use Psalm\Type\Union;

use function count;

final class TreeMapperPsalmPlugin implements MethodReturnTypeProviderInterface
{
    public static function getClassLikeNames(): array
    {
        return [TreeMapper::class];
    }

    public static function getMethodReturnType(MethodReturnTypeProviderEvent $event): ?Union
    {
        if ($event->getMethodNameLowercase() !== 'map') {
            return null;
        }

        $arguments = $event->getCallArgs();

        if (count($arguments) === 0) {
            return null;
        }

        $type = $event->getSource()->getNodeTypeProvider()->getType($arguments[0]->value);

        if (! $type) {
            return null;
        }

        $types = [];

        foreach ($type->getAtomicTypes() as $node) {
            $inferred = self::type($node);

            if ($inferred === null) {
                return null;
            }

            $types[] = $inferred;
        }

        return Type::combineUnionTypeArray($types, $event->getSource()->getCodebase());
    }

    private static function type(Atomic $node): ?Union
    {
        return match (true) {
            $node instanceof TLiteralString => Type::parseString($node->value),
            $node instanceof TDependentGetClass => $node->as_type,
            $node instanceof TClassString && $node->as_type => new Union([$node->as_type]),
            default => null,
        };
    }
}
