<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\IntegerType;
use CuyZ\Valinor\Type\ObjectType;
use CuyZ\Valinor\Type\Parser\Exception\Generic\AssignedGenericNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Generic\CannotAssignGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\InvalidAssignedGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\MissingGenerics;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidClassTemplate;
use CuyZ\Valinor\Type\Parser\Exception\Template\InvalidTemplate;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassAliasSpecification;
use CuyZ\Valinor\Type\Parser\Factory\Specifications\ClassContextSpecification;
use CuyZ\Valinor\Type\Parser\Factory\TypeParserFactory;
use CuyZ\Valinor\Type\Parser\LazyParser;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Parser\Template\TemplateParser;
use CuyZ\Valinor\Type\StringType;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\ArrayKeyType;
use CuyZ\Valinor\Type\Types\ClassType;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Utility\Reflection\Reflection;

use function array_keys;
use function array_shift;
use function array_slice;
use function count;

/** @internal */
final class GenericClassNameToken implements TraversingToken
{
    /** @var class-string */
    private string $className;

    private TypeParserFactory $typeParserFactory;

    private TemplateParser $templateParser;

    /**
     * @param class-string $className
     */
    public function __construct(string $className, TypeParserFactory $typeParserFactory, TemplateParser $templateParser)
    {
        $this->className = $className;
        $this->typeParserFactory = $typeParserFactory;
        $this->templateParser = $templateParser;
    }

    public function traverse(TokenStream $stream): ObjectType
    {
        $reflection = Reflection::class($this->className);

        try {
            $docComment = $reflection->getDocComment() ?: '';
            $parser = new LazyParser(
                fn () => $this->typeParserFactory->get(
                    new ClassContextSpecification($this->className),
                    new ClassAliasSpecification($this->className)
                )
            );

            $templates = $this->templateParser->templates($docComment, $parser);
        } catch (InvalidTemplate $exception) {
            throw new InvalidClassTemplate($this->className, $exception);
        }

        $generics = $this->generics($stream, $templates);
        $generics = $this->assignGenerics($templates, $generics);

        return $reflection->isInterface() || $reflection->isAbstract()
            ? new InterfaceType($this->className, $generics)
            : new ClassType($this->className, $generics);
    }

    /**
     * @param array<string, Type> $templates
     * @return Type[]
     */
    private function generics(TokenStream $stream, array $templates): array
    {
        if ($stream->done() || ! $stream->next() instanceof OpeningBracketToken) {
            return [];
        }

        $generics = [];

        $stream->forward();

        while (true) {
            if ($stream->done()) {
                throw new MissingGenerics($this->className, $generics, $templates);
            }

            $generics[] = $stream->read();

            if ($stream->done()) {
                throw new GenericClosingBracketMissing($this->className, $generics);
            }

            $next = $stream->forward();

            if ($next instanceof ClosingBracketToken) {
                break;
            }

            if (! $next instanceof CommaToken) {
                throw new GenericCommaMissing($this->className, $generics);
            }
        }

        return $generics;
    }

    /**
     * @param array<string, Type> $templates
     * @param Type[] $generics
     * @return array<string, Type>
     */
    private function assignGenerics(array $templates, array $generics): array
    {
        $assignedGenerics = [];

        foreach ($templates as $name => $template) {
            $generic = array_shift($generics);

            if ($generic === null) {
                $remainingTemplates = array_keys(array_slice($templates, count($assignedGenerics)));

                throw new AssignedGenericNotFound($this->className, ...$remainingTemplates);
            }

            if ($template instanceof ArrayKeyType && $generic instanceof StringType) {
                $generic = ArrayKeyType::string();
            }

            if ($template instanceof ArrayKeyType && $generic instanceof IntegerType) {
                $generic = ArrayKeyType::integer();
            }

            if (! $generic->matches($template)) {
                throw new InvalidAssignedGeneric($generic, $template, $name, $this->className);
            }

            $assignedGenerics[$name] = $generic;
        }

        if (! empty($generics)) {
            throw new CannotAssignGeneric($this->className, ...$generics);
        }

        return $assignedGenerics;
    }
}
