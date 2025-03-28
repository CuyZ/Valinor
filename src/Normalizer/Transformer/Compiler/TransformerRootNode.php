<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\MethodNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Transformer;
use CuyZ\Valinor\Type\Type;
use WeakMap;

/** @internal */
final class TransformerRootNode extends Node
{
    public function __construct(
        private TransformerDefinitionBuilder $definitionBuilder,
        private Type $type,
    ) {}

    public function compile(Compiler $compiler): Compiler
    {
        $definition = $this->definitionBuilder->for($this->type);
        $definition = $definition->markAsSure();

        $classNode = $this->transformerClassNode($definition);
        $classNode = $definition->typeFormatter()->manipulateTransformerClass($classNode, $this->definitionBuilder);

        return $classNode->compile($compiler);
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
                    )
                    ->withReturnType('mixed')
                    ->withBody(
                        Node::variable('references')->assign(Node::newClass(WeakMap::class))->asExpression(),
                        Node::return(
                            $definition->typeFormatter()->formatValueNode(
                                Node::variable('value'),
                            ),
                        ),
                    ),
            );
    }
}
