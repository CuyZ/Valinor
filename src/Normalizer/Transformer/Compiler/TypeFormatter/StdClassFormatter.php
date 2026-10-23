<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Normalizer\Transformer\EmptyObject;
use CuyZ\Valinor\Type\Types\MixedType;
use stdClass;
use WeakMap;

use function CuyZ\Valinor\Compiler\{call, castToArray, className, if_, param, return_, shortClosure, this, value, variable};

/** @internal */
final class StdClassFormatter implements TypeFormatter
{
    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        if ($class->hasMethod('transform_stdclass')) {
            return $class;
        }

        $defaultDefinition = $definitionBuilder->for(MixedType::get());

        $class = $defaultDefinition->typeFormatter()->manipulateTransformerClass($class, $definitionBuilder);

        return $class->withMethod(
            name: 'transform_stdclass',
            parameters: [
                param('value', stdClass::class),
                param('references', WeakMap::class),
            ],
            returnType: 'mixed',
            body: [
                variable('values')->assign(castToArray(variable('value')))->asStatement(),
                if_(
                    condition: variable('values')->equals(value([])),
                    body: return_(
                        className(EmptyObject::class)->callStaticMethod('get'),
                    ),
                ),
                return_(
                    call(
                        name: 'array_map',
                        arguments: [
                            shortClosure(
                                return: $defaultDefinition->typeFormatter()->formatValueNode(variable('value')),
                                parameters: [param('value', 'mixed')],
                            ),
                            castToArray(variable('value')),
                        ],
                    ),
                ),
            ],
        );
    }

    public function formatValueNode(Node $valueNode): Node
    {
        return this()->callMethod(
            method: 'transform_stdclass',
            arguments: [
                $valueNode,
                variable('references'),
            ],
        );
    }
}
