<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Type;

use function str_contains;

/** @internal */
final class TypeConditionTransformerNode implements TypeTransformer
{
    public function __construct(
        private Type $type,
        private TypeTransformer $next,
    ) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        // @todo use nativeType
        if (! $this->shouldTodo()) {
            return $this->next->valueTransformationNode($valueNode);
        }

        return Node::this()->callMethod($this->methodName(), [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        $class = $this->next->manipulateTransformerClass($class);

        if (! $this->shouldTodo()) {
            return $class;
        }

        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                )
                ->withReturnType('mixed')
                ->withBody(
                    Node::if(
                        condition: Node::negate(new TypeAcceptNode($this->type)),
                        body: Node::return(
                            Node::this()
                                ->access('delegate')
                                ->callMethod('transform', [Node::variable('value')]),
                        ),
                    ),
                    Node::return(
                        $this->next->valueTransformationNode(Node::variable('value')),
                    ),
                ),
        );
    }

    private function shouldTodo(): bool
    {
        if ($this->type instanceof ObjectType && str_contains($this->type->className(), '@anonymous')) {
            return false;
        }

        return true;
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->type->toString()));

        return 'transform_unsure_' . $slug . '_' . sha1($this->type->toString());
    }
}
