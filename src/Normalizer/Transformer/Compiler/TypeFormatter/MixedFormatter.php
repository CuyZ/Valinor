<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Types\IterableType;
use CuyZ\Valinor\Type\Types\NativeBooleanType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\NativeFloatType;
use CuyZ\Valinor\Type\Types\NativeIntegerType;
use CuyZ\Valinor\Type\Types\NativeStringType;
use CuyZ\Valinor\Type\Types\NullType;
use DateTime;
use DateTimeZone;
use UnitEnum;
use WeakMap;

use function CuyZ\Valinor\Compiler\{if_, param, return_, this, variable};

/** @internal */
final class MixedFormatter implements TypeFormatter
{
    public function formatValueNode(Node $valueNode): Node
    {
        return this()->callMethod(
            method: 'transform_mixed',
            arguments: [
                $valueNode,
                variable('references'),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        if ($class->hasMethod('transform_mixed')) {
            return $class;
        }

        // This is a placeholder method to avoid infinite loops.
        $class = $class->withMethod('transform_mixed');

        $nodes = [];

        $types = [
            NativeBooleanType::get(),
            NativeFloatType::get(),
            NativeIntegerType::get(),
            NativeStringType::get(),
            NullType::get(),
            new NativeClassType(UnitEnum::class),
            new NativeClassType(DateTime::class),
            new NativeClassType(DateTimeZone::class),
            IterableType::native(),
        ];

        foreach ($types as $type) {
            $definition = $definitionBuilder->for($type)->markAsSure();

            $class = $definition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

            $nodes[] = if_(
                condition: new TypeAcceptNode(variable('value'), $definition->type),
                body: return_($definition->typeFormatter()->formatValueNode(variable('value'))),
            );
        }

        $nodes[] = return_(
            this()
                ->access('delegate')
                ->callMethod('transform', [
                    variable('value'),
                ]),
        );

        return $class->withMethod(
            name: 'transform_mixed',
            parameters: [
                param('value', 'mixed'),
                param('references', WeakMap::class),
            ],
            returnType: 'mixed',
            body: $nodes,
        );
    }
}
