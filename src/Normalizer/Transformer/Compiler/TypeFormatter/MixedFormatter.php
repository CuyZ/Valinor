<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
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

/** @internal */
final class MixedFormatter implements TypeFormatter
{
    public function formatValueNode(ComplianceNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: 'transform_mixed',
            arguments: [
                $valueNode,
                Node::variable('references'),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        if ($class->hasMethod('transform_mixed')) {
            return $class;
        }

        // This is a placeholder method to avoid infinite loops.
        $class = $class->withMethods(Node::method('transform_mixed'));

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

            $nodes[] = Node::if(
                condition: new TypeAcceptNode(Node::variable('value'), $definition->type),
                body: Node::return($definition->typeFormatter()->formatValueNode(Node::variable('value'))),
            );
        }

        $nodes[] = Node::return(
            Node::this()
                ->access('delegate')
                ->callMethod('transform', [
                    Node::variable('value'),
                ]),
        );

        return $class->withMethods(
            Node::method('transform_mixed')
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('mixed')
                ->withBody(...$nodes),
        );
    }
}
