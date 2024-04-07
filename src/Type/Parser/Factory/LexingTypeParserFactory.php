<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Factory;

use CuyZ\Valinor\Type\GenericType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\CachedParser;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeParserSpecification;
use CuyZ\Valinor\Type\Parser\GenericCheckerParser;
use CuyZ\Valinor\Type\Parser\Lexer\NativeLexer;
use CuyZ\Valinor\Type\Parser\Lexer\SpecificationsLexer;
use CuyZ\Valinor\Type\Parser\LexingParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionFunction;

/** @internal */
final class LexingTypeParserFactory implements TypeParserFactory
{
    private TypeParser $nativeParser;

    public function buildNativeTypeParserForClass(string $className): TypeParser
    {
        return $this->buildTypeParser(
            new ClassContextSpecification($className),
        );
    }

    public function buildAdvancedTypeParserForClass(ObjectType $type, array $aliases = []): TypeParser
    {
        $specifications = [
            new ClassContextSpecification($type->className()),
            new AliasSpecification(Reflection::class($type->className())),
        ];

        if ($aliases === [] && $type instanceof GenericType) {
            $aliases = $type->generics();
        }

        if ($aliases !== []) {
            $specifications[] = new TypeAliasAssignerSpecification($aliases);
        }

        $parser = $this->buildTypeParser(...$specifications);

        return new GenericCheckerParser($parser, $this);
    }

    public function buildNativeTypeParserForFunction(ReflectionFunction $reflection): TypeParser
    {
        $class = $reflection->getClosureScopeClass();

        if ($class) {
            return $this->buildNativeTypeParserForClass($class->name);
        }

        return $this->buildDefaultTypeParser();
    }

    public function buildAdvancedTypeParserForFunction(ReflectionFunction $reflection): TypeParser
    {
        $class = $reflection->getClosureScopeClass();

        $specifications = [
            new AliasSpecification($reflection),
        ];

        if ($class === null) {
            return $this->buildTypeParser(...$specifications);
        }

        $specifications[] = new ClassContextSpecification($class->name);

        $parser = $this->buildTypeParser(...$specifications);

        return new GenericCheckerParser($parser, $this);
    }

    public function buildDefaultTypeParser(): TypeParser
    {
        return $this->nativeParser ??= new CachedParser($this->buildTypeParser());
    }

    private function buildTypeParser(TypeParserSpecification ...$specifications): TypeParser
    {
        $lexer = new SpecificationsLexer($specifications);
        $lexer = new NativeLexer($lexer);

        return new LexingParser($lexer);
    }
}
