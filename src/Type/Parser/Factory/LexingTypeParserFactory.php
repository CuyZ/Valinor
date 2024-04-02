<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory;

use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeParserSpecification;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\Lexer\SpecificationsLexer;
use CuyZ\Valinor\Type\Parser\LexingParser;
use CuyZ\Valinor\Type\Parser\TypeParser;

/** @internal */
final class LexingTypeParserFactory implements TypeParserFactory
{
    private TypeParser $nativeParser;

    public function get(TypeParserSpecification ...$specifications): TypeParser
    {
        if ($specifications === []) {
            return $this->nativeParser ??= new CachedParser($this->buildTypeParser());
        }

        return $this->buildTypeParser(...$specifications);
    }

    private function buildTypeParser(TypeParserSpecification ...$specifications): TypeParser
    {
        $lexer = new SpecificationsLexer($specifications);
        $lexer = new NativeLexer($lexer);

        $parser = new LexingParser($lexer);

        foreach ($specifications as $specification) {
            $parser = $specification->manipulateParser($parser, $this);
        }

        return $parser;
    }
}
