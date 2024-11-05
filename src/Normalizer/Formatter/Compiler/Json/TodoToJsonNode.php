<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler\Json;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class TodoToJsonNode implements TypeTransformer
{
    public function __construct(private TypeTransformer $delegate) {}

    public function valueTransformationNode(CompliantNode $valueNode): Node
    {
        return Node::functionCall('fwrite', [
            Node::variable('formatter')->access('resource'),
            $this->delegate->valueTransformationNode($valueNode),
        ])->asExpression();
    }

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode
    {
        return $this->delegate->manipulateTransformerClass($class);
    }
}
