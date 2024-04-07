<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use function array_merge;
use function current;
use function trim;

/** @internal */
final class Annotations
{
    /** @var array<non-empty-string, non-empty-list<TokenizedAnnotation>> */
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
                    $this->annotations[$token][] = new TokenizedAnnotation($current);
                }

                $current = [];
            } else {
                array_unshift($current, $token);
            }
        }
    }

    public function firstOf(string ...$annotations): ?string
    {
        foreach ($annotations as $annotation) {
            if (isset($this->annotations[$annotation])) {
                return $this->annotations[$annotation][0]->raw();
            }
        }

        return null;
    }

    /**
     * @return list<TokenizedAnnotation>
     */
    public function allOf(string ...$annotations): array
    {
        $all = [];

        foreach ($annotations as $annotation) {
            if (isset($this->annotations[$annotation])) {
                $all = array_merge($all, $this->annotations[$annotation]);
            }
        }

        return $all;
    }

    private function sanitizeDocComment(string $value): string
    {
        $value = preg_replace('#^\s*/\*\*([^/]+)\*/\s*$#', '$1', $value);

        return preg_replace('/^\s*\*\s*(\S*)/mU', '$1', $value); // @phpstan-ignore-line / We know the regex is correct
    }

    /**
     * @param array<string> $array
     * @return array<string>
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
