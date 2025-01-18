<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\MixedDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use WeakMap;

/** @internal */
final class MixedToArrayFormatter implements TypeFormatter
{
    public function __construct(
        private MixedDefinitionNode $mixed,
    ) {}

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: 'transform_mixed',
            arguments: [
                $valueNode,
                Node::variable('formatter'),
                Node::variable('references'),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        foreach ($this->mixed->definitions as $definition) {
            $class = $definition->typeFormatter()->manipulateTransformerClass($class);
        }

        if ($class->hasMethod('transform_mixed')) {
            return $class;
        }

        return $class->withMethods(
            Node::method('transform_mixed')
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                    Node::parameterDeclaration('formatter', Formatter::class),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('mixed')
                ->withBody(...$this->transformationNodes()),
        );
    }

    /**
     * @return list<Node>
     */
    private function transformationNodes(): array
    {
        $nodes = [];

        foreach ($this->mixed->definitions as $definition) {
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
                    Node::variable('formatter'),
                ]),
        );

        return $nodes;
    }
}
