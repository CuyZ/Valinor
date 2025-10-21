<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Mapper\Source\Modifier;

use function count;

/** @internal */
final class Mapping
{
    /** @var array<string> */
    private array $keys;

    private string $to;

    private int $depth;

    /**
     * @param array<string> $keys
     */
    public function __construct(array $keys, string $to)
    {
        $this->keys = $keys;
        $this->to = $to;
        $this->depth = count($keys) - 1;
    }

    public function matches(int|string $key, int $atDepth): bool
    {
        $from = $this->keys[$atDepth] ?? null;

        return $from === (string)$key || $from === '*';
    }

    public function findMappedKey(int|string $key, int $atDepth): ?string
    {
        if ($atDepth < $this->depth
            || !$this->matches($key, $atDepth)
        ) {
            return null;
        }

        return $this->to;
    }
}
