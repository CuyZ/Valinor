<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\MixedType;
use WeakMap;

use function CuyZ\Valinor\Compiler\{if_, negate, param, return_, this, variable};
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

    public function formatValueNode(Node $valueNode): Node
    {
        return this()->callMethod(
            method: $this->methodName(),
            arguments: [
                $valueNode,
                variable('references'),
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

        return $class->withMethod(
            name: $methodName,
            parameters: [
                param('value', 'mixed'),
                param('references', WeakMap::class),
            ],
            returnType: 'mixed',
            body: [
                if_(
                    condition: negate(
                        (new TypeAcceptNode(variable('value'), $this->unsureType->nativeType()))->wrap(),
                    ),
                    body: return_($defaultDefinition->typeFormatter()->formatValueNode(variable('value'))),
                ),
                return_(
                    $this->delegate->formatValueNode(variable('value')),
                ),
            ],
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
