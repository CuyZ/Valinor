<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\ComplianttNode;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
interface TypeTransformer
{
    public function valueTransformationNode(ComplianttNode $valueNode): Node;

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode;
}
