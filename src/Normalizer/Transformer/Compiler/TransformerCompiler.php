<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Normalizer\Transformer\Compiler;

use CuyZ\Valinor\Compiler\Compiler;
use CuyZ\Valinor\Compiler\Node;
use CuyZ\Valinor\Normalizer\Formatter\Formatter;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Definition\TransformerDefinitionBuilder;
use CuyZ\Valinor\Normalizer\Transformer\Compiler\Node\TransformerRootNode;
use CuyZ\Valinor\Normalizer\Transformer\Transformer;
use CuyZ\Valinor\Type\Type;

/** @internal */
final class TransformerCompiler
{
    public function __construct(
        private TransformerDefinitionBuilder $definitionBuilder,
    ) {}

    public function compileFor(Type $type, Formatter $formatter): string
    {
        $definition = $this->definitionBuilder->for($type, $formatter->compiler());

        $rootNode = new TransformerRootNode($definition, $formatter);

        $node = Node::shortClosure($rootNode)
            ->witParameters(
                Node::parameterDeclaration('transformers', 'array'),
                Node::parameterDeclaration('formatter', $formatter::class),
                Node::parameterDeclaration('delegate', Transformer::class),
            );

        return (new Compiler())->compile($node)->code();
    }
}
