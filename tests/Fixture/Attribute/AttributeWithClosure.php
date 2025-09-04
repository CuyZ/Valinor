<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Attribute;

use Attribute;
use Closure;

#[Attribute]
final class AttributeWithClosure
{
    public function __construct(
        public Closure $closure,
    ) {}
}
