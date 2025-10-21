<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use CuyZ\Valinor\Type\Parser\Exception\Constant\ClassConstantCaseNotFound;
use CuyZ\Valinor\Type\Parser\Exception\Constant\MissingClassConstantCase;
use CuyZ\Valinor\Type\Parser\Exception\Generic\CannotAssignGeneric;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericClosingBracketMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\GenericCommaMissing;
use CuyZ\Valinor\Type\Parser\Exception\Generic\MissingGenerics;
use CuyZ\Valinor\Type\Parser\Lexer\TokenStream;
use CuyZ\Valinor\Type\Type;
use CuyZ\Valinor\Type\Types\Factory\ValueTypeFactory;
use CuyZ\Valinor\Type\Types\InterfaceType;
use CuyZ\Valinor\Type\Types\NativeClassType;
use CuyZ\Valinor\Type\Types\UnionType;
use CuyZ\Valinor\Utility\Reflection\Annotations;
use CuyZ\Valinor\Utility\Reflection\Reflection;
use ReflectionClass;
use ReflectionClassConstant;

use function array_map;
use function array_values;
use function count;
use function current;
use function explode;
use function preg_match;
use function reset;

/** @internal */
final class ClassNameToken implements TraversingToken
{
    /** @var ReflectionClass<covariant object> */
    private ReflectionClass $reflection;

    private bool $mustCheckTemplates = false;

    /**
     * @param class-string $className
     */
    public function __construct(string $className)
    {
        $this->reflection = Reflection::class($className);
    }

    public function mustCheckTemplates(): self
    {
        $self = clone $this;
        $self->mustCheckTemplates = true;

        return $self;
    }

    public function traverse(TokenStream $stream): Type
    {
        $constant = $this->classConstant($stream);

        if ($constant) {
            return $constant;
        }

        $generics = [];

        if ($this->mustCheckTemplates) {
            $templates = $this->templates($this->reflection->name);
            $generics = $this->generics($stream, $this->reflection->name);

            if (count($templates) > count($generics)) {
                throw new MissingGenerics($this->reflection->name, $generics, $templates);
            }

            if (count($generics) > count($templates)) {
                throw new CannotAssignGeneric($this->reflection->name, $templates, $generics);
            }
        }

        if ($this->reflection->isInterface()) {
            return new InterfaceType($this->reflection->name, $generics);
        }

        return new NativeClassType($this->reflection->name, $generics);
    }

    public function symbol(): string
    {
        return $this->reflection->name;
    }

    private function classConstant(TokenStream $stream): ?Type
    {
        if ($stream->done() || ! $stream->next() instanceof DoubleColonToken) {
            return null;
        }

        $stream->forward();

        if ($stream->done()) {
            throw new MissingClassConstantCase($this->reflection->name);
        }

        $symbol = $stream->forward()->symbol();

        $cases = [];

        if (! preg_match('/\*\s*\*/', $symbol)) {
            $finder = new CaseFinder($this->reflection->getConstants(ReflectionClassConstant::IS_PUBLIC));
            $cases = $finder->matching(explode('*', $symbol));
        }

        if (empty($cases)) {
            throw new ClassConstantCaseNotFound($this->reflection->name, $symbol);
        }

        $cases = array_map(static fn ($value) => ValueTypeFactory::from($value), $cases);

        if (count($cases) > 1) {
            return UnionType::from(...array_values($cases));
        }

        return reset($cases);
    }

    /**
     * @param class-string $className
     * @return list<Type>
     */
    private function generics(TokenStream $stream, string $className): array
    {
        if ($stream->done() || ! $stream->next() instanceof OpeningBracketToken) {
            return [];
        }

        $generics = [];

        $stream->forward();

        while (! $stream->done()) {
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
     * @return list<non-empty-string>
     */
    private function templates(string $className): array
    {
        $templates = [];

        $annotations = Annotations::forTemplates(Reflection::class($className));

        foreach ($annotations as $annotation) {
            $tokens = $annotation->filtered();

            $templates[] = current($tokens);
        }

        return $templates;
    }
}
