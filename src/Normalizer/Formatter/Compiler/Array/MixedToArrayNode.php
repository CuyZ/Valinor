<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler\Array;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AggregateNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\MixedDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class MixedToArrayNode implements TypeTransformer
{
    public function __construct(
        private MixedDefinitionNode $mixed,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod('transform_mixed', [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        foreach ($this->mixed->definitions as $definition) {
            // @todo
//            $class = $definition->manipulateTransformerClass($class);
        }

        $methodName = 'transform_mixed';

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                )
                ->withReturnType('mixed')
                ->withBody($this->scalarTransformationNodes()),
        );
    }

    public function scalarTransformationNodes(): Node
    {
        $nodes = [];

        foreach ($this->mixed->definitions as $definition) {
            if (! $definition->hasTransformation()) {
                continue;
            }

            $nodes[] = Node::if(
                condition: new TypeAcceptNode($definition->type),
                body: Node::return($definition->valueTransformationNode(Node::variable('value'))),
            );
        }

        $nodes[] = Node::if(
            condition: Node::functionCall('is_scalar', [Node::variable('value')]),
            body: Node::return(Node::variable('value')),
        );

        $nodes[] = Node::return(
            Node::this()
                ->access('delegate')
                ->callMethod('transform', [
                    Node::variable('value'),
                    Node::this()->access('formatter'),
                ]),
        );

        return new AggregateNode(...$nodes);
    }
}
