<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Formatter\Compiler;

use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\ArrayObjectToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\ClassToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\DateTimeToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\DateTimeZoneToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\EnumToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\IterableToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\MixedToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\NullToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\ScalarToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\ShapedArrayToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\StdClassToArrayNode;
use CuyZ\Valinor\Normalizer\Formatter\Compiler\Array\UnitEnumToArrayNode;
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
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\UnitEnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeTransformer\TypeTransformer;

final class ArrayFormatterCompiler implements FormatterCompiler
{
    public function typeTransformer(DefinitionNode $definitionNode): TypeTransformer
    {
        return match ($definitionNode::class) {
            ArrayObjectDefinitionNode::class => new ArrayObjectToArrayNode(),
            ClassDefinitionNode::class => new ClassToArrayNode($definitionNode),
            DateTimeDefinitionNode::class => new DateTimeToArrayNode(),
            DateTimeZoneDefinitionNode::class => new DateTimeZoneToArrayNode(),
            EnumDefinitionNode::class => new EnumToArrayNode($definitionNode),
            IterableDefinitionNode::class => new IterableToArrayNode($definitionNode),
            MixedDefinitionNode::class => new MixedToArrayNode($definitionNode),
            NullDefinitionNode::class => new NullToArrayNode(),
            ScalarDefinitionNode::class => new ScalarToArrayNode(),
            ShapedArrayDefinitionNode::class => new ShapedArrayToArrayNode($definitionNode),
            StdClassDefinitionNode::class => new StdClassToArrayNode(),
            UnitEnumDefinitionNode::class => new UnitEnumToArrayNode(),
        };
    }
}
