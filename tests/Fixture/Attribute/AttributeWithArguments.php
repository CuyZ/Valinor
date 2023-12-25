<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Attribute;

use Attribute;

#[Attribute]
final class AttributeWithArguments
{
    public function __construct(
        public string $foo,
        public string $bar
    ) {}
}
