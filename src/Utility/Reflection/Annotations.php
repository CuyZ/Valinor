<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility\Reflection;

use CuyZ\Valinor\Type\Parser\Lexer\TokenizedAnnotation;
use CuyZ\Valinor\Type\Parser\Lexer\TokensExtractor;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionProperty;

use function array_filter;
use function array_merge;
use function array_pop;
use function array_shift;
use function array_unshift;
use function array_values;
use function current;
use function end;
use function in_array;
use function preg_replace;
use function str_starts_with;
use function trim;

/** @internal */
final class Annotations
{
    /** @var list<TokenizedAnnotation> */
    private array $annotations = [];

    private function __construct(string|false $docBlock)
    {
        if ($docBlock === false) {
            return;
        }

        $docBlock = $this->sanitizeDocComment($docBlock);

        $tokens = (new TokensExtractor($docBlock))->all();

        $current = [];

        while (($token = array_pop($tokens)) !== null) {
            if (str_starts_with($token, '@')) {
                $current = $this->trimArrayTips($current);

                if ($current !== []) {
                    array_unshift($this->annotations, new TokenizedAnnotation($token, $current));
                }

                $current = [];
            } else {
                array_unshift($current, $token);
            }
        }
    }

    /**
     * @param class-string $className
     * @return list<TokenizedAnnotation>
     */
    public static function forImportTypes(string $className): array
    {
        return (new self(Reflection::class($className)->getDocComment()))->filteredByPriority(
            '@phpstan-import-type',
            '@psalm-import-type',
        );
    }

    /**
     * @param ReflectionClass<covariant object>|ReflectionFunctionAbstract $reflection
     * @return list<TokenizedAnnotation>
     */
    public static function forTemplates(ReflectionClass|ReflectionFunctionAbstract $reflection): array
    {
        return (new self($reflection->getDocComment()))->filteredByPriority(
            '@phpstan-template',
            '@psalm-template',
            '@template',
        );
    }

    /**
     * @param class-string $className
     * @return list<TokenizedAnnotation>
     */
    public static function forParents(string $className): array
    {
        return (new self(Reflection::class($className)->getDocComment()))->filteredByPriority(
            '@phpstan-extends',
            '@psalm-extends',
            '@extends',
        );
    }

    /**
     * @param class-string $className
     * @return list<TokenizedAnnotation>
     */
    public static function forLocalAliases(string $className): array
    {
        return (new self(Reflection::class($className)->getDocComment()))->filteredInOrder(
            '@phpstan-type',
            '@psalm-type',
        );
    }

    /**
     * @return list<TokenizedAnnotation>
     */
    public static function forParameters(ReflectionFunctionAbstract $function): array
    {
        return (new self($function->getDocComment()))->filteredByPriority(
            '@phpstan-param',
            '@psalm-param',
            '@param',
        );
    }

    public static function forProperty(ReflectionProperty $function): ?string
    {
        return (new self($function->getDocComment()))->firstOf(
            '@phpstan-var',
            '@psalm-var',
            '@var',
        )?->raw();
    }

    public static function forFunctionReturnType(ReflectionFunctionAbstract $function): ?string
    {
        return (new self($function->getDocComment()))->firstOf(
            '@phpstan-return',
            '@psalm-return',
            '@return',
        )?->raw();
    }

    private function firstOf(string ...$allowed): ?TokenizedAnnotation
    {
        foreach ($allowed as $annotation) {
            foreach ($this->annotations as $tokenizedAnnotation) {
                if ($tokenizedAnnotation->name() === $annotation) {
                    return $tokenizedAnnotation;
                }
            }
        }

        return null;
    }

    /**
     * @return list<TokenizedAnnotation>
     */
    private function filteredInOrder(string ...$allowed): array
    {
        return array_values(array_filter(
            $this->annotations,
            static fn (TokenizedAnnotation $tokenizedAnnotation) => in_array($tokenizedAnnotation->name(), $allowed, true),
        ));
    }

    /**
     * @return list<TokenizedAnnotation>
     */
    private function filteredByPriority(string ...$allowed): array
    {
        $result = [];

        foreach ($allowed as $annotation) {
            $filtered = array_filter(
                $this->annotations,
                static fn (TokenizedAnnotation $tokenizedAnnotation) => $tokenizedAnnotation->name() === $annotation,
            );

            $result = array_merge($result, $filtered);
        }

        return $result;
    }

    private function sanitizeDocComment(string $value): string
    {
        $value = preg_replace('#^\s*/\*\*([^/]+)\*/\s*$#', '$1', $value);

        return preg_replace('/^\s*\*\s*(\S*)/mU', '$1', $value); // @phpstan-ignore-line / We know the regex is correct
    }

    /**
     * @param list<string> $array
     * @return list<string>
     */
    private function trimArrayTips(array $array): array
    {
        if ($array !== [] && trim(current($array)) === '') {
            array_shift($array);
        }

        if ($array !== [] && trim(end($array)) === '') {
            array_pop($array);
        }

        return $array;
    }
}
