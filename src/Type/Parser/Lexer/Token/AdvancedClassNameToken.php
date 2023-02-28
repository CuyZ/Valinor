<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Generic\CannotAssignGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidExtendTagClassName;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidExtendTagType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\MissingGenerics;
use CuyZ\Valinor\Type\Parser\Exception\Generic\ExtendTagTypeError;
use CuyZ\Valinor\Type\Parser\Exception\Generic\SeveralExtendTagsFound;
use CuyZ\Valinor\Type\Parser\Exception\InvalidType;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidTemplate;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\AliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\TypeAliasAssignerSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\LazyParser;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Parser\Template\TemplateParser;
use CuyZ\Valinor\Type\Parser\TypeParser;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\ClassType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;

use function array_keys;
use function array_shift;
use function array_slice;
use function count;

/** @internal */
final class AdvancedClassNameToken implements TraversingToken
{
    public function __construct(
        private ClassNameToken $delegate,
        private TypeParserFactory $typeParserFactory,
        private TemplateParser $templateParser
    ) {
    }

    public function traverse(TokenStream $stream): Type
    {
        $type = $this->delegate->traverse($stream);

        if (! $type instanceof ClassType) {
            return $type;
        }

        $className = $type->className();
        $reflection = Reflection::class($className);
        $parentReflection = $reflection->getParentClass();

        $specifications = [
            new ClassContextSpecification($className),
            new AliasSpecification($reflection),
        ];

        try {
            $docComment = $reflection->getDocComment() ?: '';
            $parser = new LazyParser(
                fn () => $this->typeParserFactory->get(...$specifications)
            );

            $templates = $this->templateParser->templates($docComment, $parser);
        } catch (InvalidTemplate $exception) {
            throw new InvalidClassTemplate($className, $exception);
        }

        $generics = $this->generics($stream, $className, $templates);
        $generics = $this->assignGenerics($className, $templates, $generics);

        $parserWithGenerics = new LazyParser(
            fn () => $this->typeParserFactory->get(new TypeAliasAssignerSpecification($generics), ...$specifications)
        );

        if ($parentReflection) {
            $parentType = $this->parentType($reflection, $parentReflection, $parserWithGenerics);
        }

        return new NativeClassType($className, $generics, $parentType ?? null);
    }

    public function symbol(): string
    {
        return $this->delegate->symbol();
    }

    /**
     * @param array<string, Type> $templates
     * @param class-string $className
     * @return Type[]
     */
    private function generics(TokenStream $stream, string $className, array $templates): array
    {
        if ($stream->done() || ! $stream->next() instanceof OpeningBracketToken) {
            return [];
        }

        $generics = [];

        $stream->forward();

        while (true) {
            if ($stream->done()) {
                throw new MissingGenerics($className, $generics, $templates);
            }

            $generics[] = $stream->read();

            if ($stream->done()) {
                throw new GenericClosingBracketMissing($className, $generics);
            }

            $next = $stream->forward();

            if ($next instanceof ClosingBracketToken) {
                break;
            }

            if (! $next instanceof CommaToken) {
                throw new GenericCommaMissing($className, $generics);
            }
        }

        return $generics;
    }

    /**
     * @param class-string $className
     * @param array<string, Type> $templates
     * @param Type[] $generics
     * @return array<string, Type>
     */
    private function assignGenerics(string $className, array $templates, array $generics): array
    {
        $assignedGenerics = [];

        foreach ($templates as $name => $template) {
            $generic = array_shift($generics);

            if ($generic === null) {
                $remainingTemplates = array_keys(array_slice($templates, count($assignedGenerics)));

                throw new AssignedGenericNotFound($className, ...$remainingTemplates);
            }

            if ($template instanceof ArrayKeyType && $generic instanceof StringType) {
                $generic = ArrayKeyType::string();
            }

            if ($template instanceof ArrayKeyType && $generic instanceof IntegerType) {
                $generic = ArrayKeyType::integer();
            }

            if (! $generic->matches($template)) {
                throw new InvalidAssignedGeneric($generic, $template, $name, $className);
            }

            $assignedGenerics[$name] = $generic;
        }

        if (! empty($generics)) {
            throw new CannotAssignGeneric($className, ...$generics);
        }

        return $assignedGenerics;
    }

    /**
     * @param ReflectionClass<object> $reflection
     * @param ReflectionClass<object> $parentReflection
     */
    private function parentType(ReflectionClass $reflection, ReflectionClass $parentReflection, TypeParser $typeParser): NativeClassType
    {
        $extendedClass = Reflection::extendedClassAnnotation($reflection);

        if (count($extendedClass) > 1) {
            throw new SeveralExtendTagsFound($reflection);
        } elseif (count($extendedClass) === 0) {
            $extendedClass = $parentReflection->name;
        } else {
            $extendedClass = $extendedClass[0];
        }

        try {
            $parentType = $typeParser->parse($extendedClass);
        } catch (InvalidType $exception) {
            throw new ExtendTagTypeError($reflection, $exception);
        }

        if (! $parentType instanceof NativeClassType) {
            throw new InvalidExtendTagType($reflection, $parentType);
        }

        if ($parentType->className() !== $parentReflection->name) {
            throw new InvalidExtendTagClassName($reflection, $parentType);
        }

        return $parentType;
    }
}
