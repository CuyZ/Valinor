<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Type\Parser;

use CuyZ\Valinor\Type\Type;

/** @internal */
final class CachedParser implements TypeParser
{
    private TypeParser $delegate;

    /** @var array<string, Type> */
    private array $types = [];

    public function __construct(TypeParser $delegate)
    {
        $this->delegate = $delegate;
    }

    public function parse(string $raw): Type
    {
        return $this->types[$raw] ??= $this->delegate->parse($raw);
    }
}
