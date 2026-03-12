<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Library\NewAttributeNode;
use CuyZ\Valinor\Compiler\Library\TypeAcceptNode;
use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Definition\AttributeDefinition;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Type;
use WeakMap;

use function array_keys;
use function array_map;
use function CuyZ\Valinor\Compiler\{if_, param, return_, shortClosure, this, value, variable};
use function preg_replace;
use function serialize;
use function sha1;
use function strtolower;

/** @internal */
final class RegisteredTransformersFormatter implements TypeFormatter
{
    public function __construct(
        private Type $type,
        private TypeFormatter $delegate,
        /** @var array<int, Type> */
        private array $transformerTypes,
        /** @var list<AttributeDefinition> */
        private array $transformerAttributes = [],
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

        $nodes = [
            variable('next')->assign(
                shortClosure(
                    $this->delegate->formatValueNode(variable('value')),
                ),
            )->asStatement(),

            ...array_map(
                fn (int $key, Type $transformerType) => if_(
                    condition: new TypeAcceptNode(variable('value'), $transformerType),
                    body: variable('next')->assign(
                        shortClosure(
                            return: this()
                                ->access('transformers')
                                ->key(value($key))
                                ->call(arguments: [
                                    variable('value'),
                                    variable('next'),
                                ]),
                        ),
                    )->asStatement(),
                ),
                array_keys($this->transformerTypes),
                $this->transformerTypes,
            ),

            ...array_map(
                function (AttributeDefinition $attribute) {
                    $node = variable('next')->assign(
                        shortClosure(
                            return: (new NewAttributeNode($attribute))
                                ->wrap()
                                ->callMethod(
                                    method: 'normalize',
                                    arguments: [
                                        variable('value'),
                                        variable('next'),
                                    ],
                                ),
                        ),
                    )->asStatement();

                    $transformerType = $attribute->class->methods->get('normalize')->parameters->at(0)->type;

                    if (! $this->type->matches($transformerType)) {
                        return if_($transformerType->compiledAccept(variable('value')), $node);
                    }

                    return $node;
                },
                $this->transformerAttributes,
            ),

            return_(
                variable('next')->call(),
            ),
        ];

        return $class->withMethod(
            name: $methodName,
            parameters: [
                param('value', 'mixed'),
                param('references', WeakMap::class),
            ],
            returnType: 'mixed',
            body: $nodes,
        );
    }

    /**
     * @return non-empty-string
     */
    private function methodName(): string
    {
        $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($this->type->toString()));

        return "transform_{$slug}_" . sha1(serialize([$this->type->toString(), $this->transformerAttributes]));
    }
}
