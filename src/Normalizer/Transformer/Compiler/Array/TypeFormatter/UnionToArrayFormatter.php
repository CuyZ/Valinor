<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\UnionDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

use function array_map;

/** @internal */
final class UnionToArrayFormatter implements TypeFormatter
{
    public function __construct(
        private UnionDefinitionNode $union,
    ) {}

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: $this->methodName(),
            arguments: [
                $valueNode,
                Node::variable('formatter'),
                Node::variable('references'),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->union->defaultDefinition->typeFormatter()->manipulateTransformerClass($class);

        foreach ($this->union->definitions as $definition) {
            $class = $definition->typeFormatter()->manipulateTransformerClass($class);
        }

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        $nodes = array_map(
            fn (TransformerDefinition $definition) => Node::if(
                condition: new TypeAcceptNode(Node::variable('value'), $definition->type),
                body: Node::return(
                    $definition->typeFormatter()->formatValueNode(Node::variable('value')),
                ),
            ),
            $this->union->definitions,
        );

        $nodes[] = Node::return(
            $this->union->defaultDefinition->typeFormatter()->formatValueNode(Node::variable('value'))
        );

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                    Node::parameterDeclaration('formatter', Formatter::class),
                    Node::parameterDeclaration('references', \WeakMap::class),
                )
                ->withReturnType('mixed')
                ->withBody(...$nodes),
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        return 'transform_union_' . hash('xxh128', $this->union->type->toString());
    }
}
