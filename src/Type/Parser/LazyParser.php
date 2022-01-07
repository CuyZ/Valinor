<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class LazyParser implements TypeParser
{
    /** @var callable(): TypeParser */
    private $callback;

    private TypeParser $delegate;

    /**
     * @param callable(): TypeParser $callback
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function parse(string $raw): Type
    {
        $this->delegate ??= ($this->callback)();

        return $this->delegate->parse($raw);
    }
}
