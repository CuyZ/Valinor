<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter;

use CuyZ\Valinor\Compiler\Native\AnonymousClassNode;
use CuyZ\Valinor\Compiler\Native\CompliantNode;
use CuyZ\Valinor\Compiler\Node;

/** @internal */
interface TypeFormatter
{
    public function formatValueNode(CompliantNode $valueNode): Node;

    public function manipulateTransformerClass(AnonymousClassNode $class): AnonymousClassNode;
}
