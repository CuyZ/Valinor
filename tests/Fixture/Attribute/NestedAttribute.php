<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Attribute;

use Attribute;

#[Attribute]
final class NestedAttribute
{
    public function __construct(
        /** @var list<object> */
        public array $nestedAttributes,
    ) {}
}
