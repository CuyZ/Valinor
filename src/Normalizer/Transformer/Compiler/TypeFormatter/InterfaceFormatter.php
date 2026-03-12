<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TransformerDefinitionBuilder;
use CuyZ\Valinor\Type\Types\InterfaceType;
use DateTimeInterface;

use function CuyZ\Valinor\Compiler\this;

/** @internal */
final class InterfaceFormatter implements TypeFormatter
{
    public function __construct(
        private InterfaceType $type,
    ) {}

    public function formatValueNode(Node $valueNode): Node
    {
        if ($this->type->className() === DateTimeInterface::class) {
            return (new DateTimeFormatter())->formatValueNode($valueNode);
        }

        return this()
            ->access('delegate')
            ->callMethod('transform', [$valueNode]);
    }

    public function manipulateTransformerClass(AnonymousClassNode $class, TransformerDefinitionBuilder $definitionBuilder): AnonymousClassNode
    {
        return $class;
    }
}
