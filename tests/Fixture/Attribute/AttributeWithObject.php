<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Attribute;

use Attribute;

#[Attribute]
final class AttributeWithObject
{
    public function __construct(
        public object $object,
    ) {}
}
