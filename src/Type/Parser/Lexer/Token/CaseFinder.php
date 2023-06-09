<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser\Lexer\Token;

use function current;
use function next;
use function strlen;
use function strpos;

/**
 * @internal
 *
 * @template CaseType
 */
final class CaseFinder
{
    public function __construct(
        /** @var array<string, CaseType> */
        private array $cases
    ) {}

    /**
     * @param list<string> $tokens
     * @return array<string, CaseType>
     */
    public function matching(array $tokens): array
    {
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
