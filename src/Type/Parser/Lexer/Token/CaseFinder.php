<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use function current;
use function next;
use function strlen;
use function strpos;

/** @internal */
final class CaseFinder
{
    public function __construct(
        /** @var array<string, mixed> */
        private array $cases
    ) {
    }

    /**
     * @param list<string> $tokens
     * @return array<string, mixed>
     */
    public function matching(array $tokens): array
    {
        if (count($tokens) === 1) {
            return isset($this->cases[$tokens[0]])
                ? [$this->cases[$tokens[0]]]
                : [];
        }
        $matches = [];

        foreach ($this->cases as $name => $value) {
            if ($this->matches($name, $tokens)) {
                $matches[$name] = $value;
            }
        }

        return $matches;
    }

    /**
     * @param list<string> $tokens
     */
    private function matches(string $name, array $tokens): bool
    {
        $offset = 0;

        while (($token = current($tokens)) !== false) {
            $next = next($tokens);

            if ($token === '') {
                if ($next === false) {
                    return true;
                }

                continue;
            }

            $position = strpos($name, $token, $offset);

            if ($position === false) {
                return false;
            }

            $offset = $position + strlen($token);
        }

        return $offset === strlen($name);
    }
}
