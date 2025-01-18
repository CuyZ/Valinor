<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Json;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\DateTimeToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\InterfaceToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DateTimeDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DateTimeZoneDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\DefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\EnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\InterfaceDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\MixedDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\NullDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ScalarDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\ShapedArrayDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\StdClassDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\TraversableDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\UnitEnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\FormatterCompiler;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Json\TypeFormatter\ClassToJsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Json\TypeFormatter\ScalarToJsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Json\TypeFormatter\TodoToJsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Json\TypeFormatter\TraversableToJsonFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;

/** @internal */
final class JsonFormatterCompiler implements FormatterCompiler
{
    public function typeTransformer(DefinitionNode $definitionNode): TypeFormatter
    {
        return match ($definitionNode::class) {
            ClassDefinitionNode::class => new ClassToJsonFormatter($definitionNode),
            DateTimeDefinitionNode::class => new TodoToJsonFormatter(new DateTimeToArrayFormatter()),
            DateTimeZoneDefinitionNode::class => null,
            EnumDefinitionNode::class => null,
            InterfaceDefinitionNode::class => new InterfaceToArrayFormatter($definitionNode),
            MixedDefinitionNode::class => null,
            NullDefinitionNode::class => null,
            ScalarDefinitionNode::class => new ScalarToJsonFormatter(),
            ShapedArrayDefinitionNode::class => null,
            StdClassDefinitionNode::class => null,
            TraversableDefinitionNode::class => new TraversableToJsonFormatter($definitionNode),
            UnitEnumDefinitionNode::class => null,
        };
    }
}
