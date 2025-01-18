<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

/** @internal */
interface FormatterCompiler
{
    public function typeTransformer(DefinitionNode $definitionNode): TypeFormatter;
}
