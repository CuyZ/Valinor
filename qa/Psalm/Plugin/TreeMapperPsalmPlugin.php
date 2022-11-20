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
        switch (true) {
            case $node instanceof TLiteralString:
                return Type::parseString($node->value);
            case $node instanceof TDependentGetClass:
                return $node->as_type;
            case $node instanceof TClassString && $node->as_type:
                return new Union([$node->as_type]);
            default:
                return null;
        }
    }
}
