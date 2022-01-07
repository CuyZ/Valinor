<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory;

use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassAliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\HandleClassGenericSpecification;
use CuyZ\Valinor\Type\Parser\Lexer\ClassAliasLexer;
use CuyZ\Valinor\Type\Parser\Lexer\ClassContextLexer;
use CuyZ\Valinor\Type\Parser\Lexer\ClassGenericLexer;
use CuyZ\Valinor\Type\Parser\Lexer\TypeAliasLexer;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\Lexer\TypeLexer;
use CuyZ\Valinor\Type\Parser\LexingParser;
use CuyZ\Valinor\Type\Parser\Template\TemplateParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use LogicException;

use function get_class;

/** @internal */
final class LexingTypeParserFactory implements TypeParserFactory
{
    private TemplateParser $templateParser;

    private TypeParser $nativeParser;

    public function __construct(TemplateParser $templateParser)
    {
        $this->templateParser = $templateParser;
    }

    public function get(object ...$specifications): TypeParser
    {
        if (empty($specifications)) {
            return $this->nativeParser ??= new CachedParser(new LexingParser(new NativeLexer()));
        }

        $lexer = new NativeLexer();

        foreach ($specifications as $specification) {
            $lexer = $this->transform($lexer, $specification);
        }

        return new LexingParser($lexer);
    }

    private function transform(TypeLexer $lexer, object $specification): TypeLexer
    {
        if ($specification instanceof ClassContextSpecification) {
            return new ClassContextLexer($lexer, $specification->className());
        }

        if ($specification instanceof ClassAliasSpecification) {
            return new ClassAliasLexer($lexer, $specification->className());
        }

        if ($specification instanceof HandleClassGenericSpecification) {
            return new ClassGenericLexer($lexer, $this, $this->templateParser);
        }

        if ($specification instanceof TypeAliasAssignerSpecification) {
            return new TypeAliasLexer($lexer, $specification->aliases());
        }

        throw new LogicException('Unhandled specification of type `' . get_class($specification) . '`.');
    }
}
