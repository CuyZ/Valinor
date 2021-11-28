<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;

#[BasicAttribute]
#[AttributeWithArguments('foo', 'bar')]
// @PHP8.0 move to anonymous class
final class ObjectWithAttributes
{
    #[BasicAttribute]
    #[AttributeWithArguments('foo', 'bar')]
    public bool $property;

    #[BasicAttribute]
    #[AttributeWithArguments('foo', 'bar')]
    public function method(#[BasicAttribute] string $parameter): void
    {
    }
}
