<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\InterfaceDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use DateTimeInterface;

/** @internal */
final class InterfaceToArrayFormatter implements TypeFormatter
{
    public function __construct(
        private InterfaceDefinitionNode $interface,
    ) {}

    public function formatValueNode(CompliantNode $valueNode): Node
    {
        if ($this->interface->type->className() === DateTimeInterface::class) {
            return (new DateTimeToArrayFormatter())->formatValueNode($valueNode);
        }

        return Node::this()
            ->access('delegate')
            ->callMethod('transform', [
                Node::variable('value'),
                Node::variable('formatter'),
            ]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
