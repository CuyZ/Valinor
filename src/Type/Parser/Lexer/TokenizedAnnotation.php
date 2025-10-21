<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer;

use function array_filter;
use function array_map;
use function array_splice;
use function implode;

/** @internal */
final class TokenizedAnnotation
{
    public function __construct(
        /** @var non-empty-string */
        private string $name,
        /** @var non-empty-list<string> */
        private array $tokens,
    ) {}

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return non-empty-string
     */
    public function allAfter(int $offset): string
    {
        $tokens = $this->tokens;

        /** @var non-empty-string */
        return implode('', array_splice($tokens, $offset));
    }

    /**
     * @return non-empty-array<int, non-empty-string>
     */
    public function filtered(): array
    {
        /** @var non-empty-array<int, non-empty-string> / We can force the type as we know for sure it contains at least one non-empty-string */
        return array_filter(
            array_map(trim(...), $this->tokens),
            static fn ($value) => $value !== '',
        );
    }

    /**
     * @return non-empty-string
     */
    public function raw(): string
    {
        /** @var non-empty-string / We can force the type as we know for sure it contains at least one non-empty-string */
        return implode('', $this->tokens);
    }
}
