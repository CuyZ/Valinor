<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler\Array;

use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\ClassToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\DateTimeToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\DateTimeZoneToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\EnumToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\InterfaceToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\MixedToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\NullToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\ScalarToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\ShapedArrayToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\StdClassToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\TraversableToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\UnionToArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Array\TypeFormatter\UnitEnumToArrayFormatter;
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
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\UnionDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\Node\UnitEnumDefinitionNode;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\FormatterCompiler;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use LogicException;

/** @internal */
final class ArrayFormatterCompiler implements FormatterCompiler
{
    public function typeTransformer(DefinitionNode $definitionNode): TypeFormatter
    {
        return match ($definitionNode::class) {
            ClassDefinitionNode::class => new ClassToArrayFormatter($definitionNode),
            DateTimeDefinitionNode::class => new DateTimeToArrayFormatter(),
            DateTimeZoneDefinitionNode::class => new DateTimeZoneToArrayFormatter(),
            EnumDefinitionNode::class => new EnumToArrayFormatter($definitionNode),
            InterfaceDefinitionNode::class => new InterfaceToArrayFormatter($definitionNode),
            MixedDefinitionNode::class => new MixedToArrayFormatter($definitionNode),
            NullDefinitionNode::class => new NullToArrayFormatter(),
            ScalarDefinitionNode::class => new ScalarToArrayFormatter(),
            ShapedArrayDefinitionNode::class => new ShapedArrayToArrayFormatter($definitionNode),
            StdClassDefinitionNode::class => new StdClassToArrayFormatter($definitionNode),
            TraversableDefinitionNode::class => new TraversableToArrayFormatter($definitionNode),
            UnionDefinitionNode::class => new UnionToArrayFormatter($definitionNode),
            UnitEnumDefinitionNode::class => new UnitEnumToArrayFormatter(),
            default => throw new LogicException('Unsupported definition node: ' . $definitionNode::class),
        };
    }
}
