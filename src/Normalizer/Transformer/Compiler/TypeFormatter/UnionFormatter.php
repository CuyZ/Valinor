<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Types\MixedType;
use CuyZ\Valinor\Type\Types\UnionType;
use WeakMap;

use function hash;

/** @internal */
final class UnionFormatter implements TypeFormatter
{
    public function __construct(
        private UnionType $type,
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

        $defaultDefinition = $definitionBuilder->for(MixedType::get());

        $class = $defaultDefinition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

        $nodes = [];

        foreach ($this->type->types() as $subType) {
            $definition = $definitionBuilder->for($subType)->markAsSure();

            $class = $definition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

            $nodes[] = Node::if(
                condition: new TypeAcceptNode(Node::variable('value'), $definition->type),
                body: Node::return(
                    $definition->typeFormatter()->formatValueNode(Node::variable('value')),
                ),
            );
        }

        $nodes[] = Node::return(
            $defaultDefinition->typeFormatter()->formatValueNode(Node::variable('value')),
        );

        return $class->withMethods(
            Node::method($methodName)
                ->witParameters(
                    Node::parameterDeclaration('value', 'mixed'),
                    Node::parameterDeclaration('references', WeakMap::class),
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
        return 'transform_union_' . hash('crc32', $this->type->toString());
    }
}
