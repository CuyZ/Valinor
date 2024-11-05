<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler;

use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\DateTimeToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Json\ClassToJsonNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Json\IterableToJsonNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Json\ScalarToJsonNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Json\TodoToJsonNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ArrayObjectDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DateTimeDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DateTimeZoneDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\EnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\IterableDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\MixedDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\NullDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ScalarDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ShapedArrayDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\StdClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class JsonFormatterCompiler implements FormatterCompiler
{
    public function typeTransformer(DefinitionNode $definitionNode): TypeTransformer
    {
        return match ($definitionNode::class) {
            ArrayObjectDefinitionNode::class => null,
            ClassDefinitionNode::class => new ClassToJsonNode($definitionNode),
            DateTimeDefinitionNode::class => new TodoToJsonNode(new DateTimeToArrayNode()),
            DateTimeZoneDefinitionNode::class => null,
            EnumDefinitionNode::class => null,
            IterableDefinitionNode::class => new IterableToJsonNode($definitionNode),
            MixedDefinitionNode::class => null,
            NullDefinitionNode::class => null,
            ScalarDefinitionNode::class => new ScalarToJsonNode(),
            ShapedArrayDefinitionNode::class => null,
            StdClassDefinitionNode::class => null,
        };
    }
}
