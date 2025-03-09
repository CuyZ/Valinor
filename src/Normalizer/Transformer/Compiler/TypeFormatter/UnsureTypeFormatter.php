<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class UnsureTypeFormatter implements TypeFormatter
{
    public function __construct(
        private TypeFormatter $delegate,
        private Type $unsureType,
    ) {}

    public function formatValueNode(ComplianceNode $valueNode): Node
    {
        return Node::this()->callMethod(
            method: $this->methodName(),
            arguments: [
                $valueNode,
                Node::variable('references'),
            ],
        );
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->delegate->manipulateTransformerClass($class);

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                    Node::parameterDeclaration('references', \WeakMap::class),
                )
                ->withReturnType('mixed')
                ->withBody(
                    Node::if(
                        condition: Node::negate(
                            (new TypeAcceptNode(Node::variable('value'), $this->unsureType))->wrap()
                        ),
                        body: Node::return(
                            Node::this()->callMethod(
                                method: 'transform_mixed',
                                arguments: [
                                    Node::variable('value'),
                                    Node::variable('references'),
                                ],
                            )
                        ),
                    ),
                    Node::return(
                        $this->delegate->formatValueNode(Node::variable('value'))
                    )
                ),
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        return 'transform_unsure_' . hash('xxh128', $this->unsureType->toString());
    }
}
