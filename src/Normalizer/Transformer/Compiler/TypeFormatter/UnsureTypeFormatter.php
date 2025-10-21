<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use WeakMap;

use function hash;
use function preg_replace;
use function strtolower;

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

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        $methodName = $this->methodName();

        if ($class->hasMethod($methodName)) {
            return $class;
        }

        $class = $this->delegate->manipulateTransformerClass($class, $definitionBuilder);

        $defaultDefinition = $definitionBuilder->for(MixedType::get());

        $class = $defaultDefinition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                    Node::parameterDeclaration('references', WeakMap::class),
                )
                ->withReturnType('mixed')
                ->withBody(
                    Node::if(
                        condition: Node::negate(
                            (new TypeAcceptNode(Node::variable('value'), $this->unsureType->nativeType()))->wrap(),
                        ),
                        body: Node::return($defaultDefinition->typeFormatter()->formatValueNode(Node::variable('value'))),
                    ),
                    Node::return(
                        $this->delegate->formatValueNode(Node::variable('value')),
                    ),
                ),
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->unsureType->toString()));

        return "transform_unsure_{$slug}_" . hash('crc32', $this->unsureType->toString());
    }
}
