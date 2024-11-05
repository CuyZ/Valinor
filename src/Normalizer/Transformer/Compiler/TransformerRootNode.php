<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\MethodNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Transformer;

/** @internal */
final class TransformerRootNode extends Node
{
    public function __construct(
        private TransformerDefinition $definition,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $transformerClassNode = $this->transformerClassNode($this->definition);
        $transformerClassNode = $this->definition->typeTransformer->manipulateTransformerClass($transformerClassNode);

        return $transformerClassNode->compile($compiler);
    }

    private function transformerClassNode(TransformerDefinition $definition): AnonymousClassNode
    {
        return Node::anonymousClass()
            ->implements(Transformer::class)
            ->withArguments(
                Node::variable('transformers'),
                Node::variable('delegate'),
            )
            ->withProperties(
                Node::propertyDeclaration('transformers', 'array'),
                Node::propertyDeclaration('delegate', Transformer::class),
            )
            ->withMethods(
                MethodNode::constructor()
                    ->withVisibility('public')
                    ->witParameters(
                        Node::parameterDeclaration('transformers', 'array'),
                        Node::parameterDeclaration('delegate', Transformer::class),
                    )
                    ->withBody(
                        Node::property('transformers')->assign(Node::variable('transformers'))->asExpression(),
                        Node::property('delegate')->assign(Node::variable('delegate'))->asExpression(),
                    ),
                Node::method('transform')
                    ->withVisibility('public')
                    ->witParameters(
                        Node::parameterDeclaration('value', 'mixed'),
                        Node::parameterDeclaration('formatter', Formatter::class),
                    )
                    ->withReturnType('mixed')
                    ->withBody(Node::return(
                        $definition->typeTransformer->valueTransformationNode(Node::variable('value')),
                    )),
            );
    }

}
