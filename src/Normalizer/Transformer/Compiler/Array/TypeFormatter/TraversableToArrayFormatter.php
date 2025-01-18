<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\TraversableDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use WeakMap;

/** @internal */
final class TraversableToArrayFormatter implements TypeFormatter
{
    public function __construct(
        private TraversableDefinitionNode $iterable,
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
        $class = $this->iterable->subDefinition->typeFormatter()->manipulateTransformerClass($class);

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'iterable'),
                    Node::parameterDeclaration('formatter', Formatter::class),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('iterable')
                ->withBody(
                    Node::variable('values')->assign(Node::array())->asExpression(),
                    Node::forEach(
                        value: Node::variable('value'),
                        key: 'key',
                        item: 'item',
                        body: Node::variable('values')->key(Node::variable('key'))->assign(
                            $this->iterable->subDefinition->typeFormatter()->formatValueNode(Node::variable('item')),
                        )->asExpression(),
                    ),
                    Node::return(Node::variable('values')),
                ),
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->iterable->subDefinition->type->toString()));

        return "transform_iterable_{$slug}_" . hash('xxh128', $this->iterable->subDefinition->type->toString());
    }
}
