<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Normalizer\TemporaryPHP85;

use Attribute;
use Closure;
use CuyZ\Valinor\Normalizer\AsTransformer;

// PHP8.5 move to \CuyZ\Valinor\Tests\Integration\Normalizer\NormalizerCompiledCodeTest
#[Attribute, AsTransformer]
final class TransformerAttributeWithCallable
{
    public function __construct(
        private Closure $callback,
    ) {}

    public function normalize(mixed $value): mixed
    {
        return ($this->callback)($value);
    }
}
