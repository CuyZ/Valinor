<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianceNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\InterfaceDefinitionNode;
use DateTimeInterface;

/** @internal */
final class InterfaceFormatter implements TypeFormatter
{
    public function __construct(
        private InterfaceDefinitionNode $interface,
    ) {}

    public function formatValueNode(ComplianceNode $valueNode): Node
    {
        if ($this->interface->type->className() === DateTimeInterface::class) {
            return (new DateTimeFormatter())->formatValueNode($valueNode);
        }

        return Node::this()
            ->access('delegate')
            ->callMethod('transform', [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $class;
    }
}
