<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory;

use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ObjectSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeParserSpecification;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\Lexer\SpecificationsLexer;
use CuyZ\Valinor\Type\Parser\LexingParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\Parser\UnresolvableTypeFinderParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;

/** @internal */
final class TypeParserFactory
{
    private TypeParser $nativeParser;

    /**
     * @param class-string $className
     */
    public function buildNativeTypeParserForClass(string $className): TypeParser
    {
        return $this->buildTypeParser(
            new ClassContextSpecification($className),
            new ObjectSpecification(mustCheckTemplates: false),
        );
    }

    /**
     * @param class-string $className
     */
    public function buildAdvancedTypeParserForClass(string $className): TypeParser
    {
        return $this->buildTypeParser(
            new ClassContextSpecification($className),
            new AliasSpecification(Reflection::class($className)),
            new ObjectSpecification(mustCheckTemplates: true),
        );
    }

    public function buildNativeTypeParserForFunction(callable $function): TypeParser
    {
        $reflection = Reflection::function($function);
        $class = $reflection->getClosureScopeClass();

        if ($class) {
            return $this->buildNativeTypeParserForClass($class->name);
        }

        return $this->buildDefaultTypeParser();
    }

    public function buildAdvancedTypeParserForFunction(callable $function): TypeParser
    {
        $reflection = Reflection::function($function);
        $class = $reflection->getClosureScopeClass();

        $specifications = [
            new AliasSpecification($reflection),
            new ObjectSpecification(mustCheckTemplates: true),
        ];

        if ($class === null) {
            return $this->buildTypeParser(...$specifications);
        }

        return $this->buildTypeParser(
            new ClassContextSpecification($class->name),
            ...$specifications,
        );
    }

    public function buildDefaultTypeParser(): TypeParser
    {
        return $this->nativeParser ??= new CachedParser(
            new UnresolvableTypeFinderParser(
                $this->buildTypeParser(new ObjectSpecification(mustCheckTemplates: true))
            )
        );
    }

    private function buildTypeParser(TypeParserSpecification ...$specifications): TypeParser
    {
        $lexer = new SpecificationsLexer($specifications);
        $lexer = new NativeLexer($lexer);

        return new LexingParser($lexer);
    }
}
