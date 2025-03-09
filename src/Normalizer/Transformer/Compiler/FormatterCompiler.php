<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler;

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
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\ClassFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\DateTimeFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\DateTimeZoneFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\EnumFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\InterfaceFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\MixedFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\NullFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\ScalarFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\ShapedArrayFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\StdClassFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TraversableFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\TypeFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\UnionFormatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\TypeFormatter\UnitEnumFormatter;
use LogicException;

/** @internal */
final class FormatterCompiler
{
    public function typeFormatter(DefinitionNode $definitionNode): TypeFormatter
    {
        return match ($definitionNode::class) {
            ClassDefinitionNode::class => new ClassFormatter($definitionNode),
            DateTimeDefinitionNode::class => new DateTimeFormatter(),
            DateTimeZoneDefinitionNode::class => new DateTimeZoneFormatter(),
            EnumDefinitionNode::class => new EnumFormatter($definitionNode),
            InterfaceDefinitionNode::class => new InterfaceFormatter($definitionNode),
            MixedDefinitionNode::class => new MixedFormatter($definitionNode),
            NullDefinitionNode::class => new NullFormatter(),
            ScalarDefinitionNode::class => new ScalarFormatter(),
            ShapedArrayDefinitionNode::class => new ShapedArrayFormatter($definitionNode),
            StdClassDefinitionNode::class => new StdClassFormatter($definitionNode),
            TraversableDefinitionNode::class => new TraversableFormatter($definitionNode),
            UnionDefinitionNode::class => new UnionFormatter($definitionNode),
            UnitEnumDefinitionNode::class => new UnitEnumFormatter(),
            default => throw new LogicException('Unsupported definition node: ' . $definitionNode::class),
        };
    }
}
