<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use function array_filter;
use function array_merge;
use function current;
use function in_array;
use function trim;

/** @internal */
final class Annotations
{
    /** @var list<TokenizedAnnotation> */
    private array $annotations = [];

    public function __construct(string $docBlock)
    {
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

    public function firstOf(string ...$annotations): ?TokenizedAnnotation
    {
        foreach ($annotations as $annotation) {
            foreach ($this->annotations as $tokenizedAnnotation) {
                if ($tokenizedAnnotation->name() === $annotation) {
                    return $tokenizedAnnotation;
                }
            }
        }

        return null;
    }

    /**
     * @return array<TokenizedAnnotation>
     */
    public function filteredInOrder(string ...$annotations): array
    {
        return array_filter(
            $this->annotations,
            static fn (TokenizedAnnotation $tokenizedAnnotation) => in_array($tokenizedAnnotation->name(), $annotations, true),
        );
    }

    /**
     * @return list<TokenizedAnnotation>
     */
    public function filteredByPriority(string ...$annotations): array
    {
        $result = [];

        foreach ($annotations as $annotation) {
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
