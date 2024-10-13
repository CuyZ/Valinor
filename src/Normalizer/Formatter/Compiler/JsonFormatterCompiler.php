<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class JsonFormatterCompiler implements FormatterCompiler
{
    private ArrayFormatterCompiler $arrayFormatterCompiler;

    public function __construct()
    {
        $this->arrayFormatterCompiler = new ArrayFormatterCompiler();
    }

    public function typeTransformer(DefinitionNode $definitionNode): TypeTransformer
    {
        return $this->arrayFormatterCompiler->typeTransformer($definitionNode);
    }
}
