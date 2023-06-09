<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Fixture\Attribute\NestedAttribute;

#[BasicAttribute]
#[AttributeWithArguments('foo', 'bar')]
#[NestedAttribute([
    new BasicAttribute(),
    new AttributeWithArguments('foo', 'bar'),
])]
final class ObjectWithNestedAttributes
{
    #[BasicAttribute]
    #[AttributeWithArguments('foo', 'bar')]
    #[NestedAttribute([
        new BasicAttribute(),
        new AttributeWithArguments('foo', 'bar'),
    ])]
    public bool $property;

    #[BasicAttribute]
    #[AttributeWithArguments('foo', 'bar')]
    #[NestedAttribute([
        new BasicAttribute(),
        new AttributeWithArguments('foo', 'bar'),
    ])]
    public function method(#[BasicAttribute] string $parameter): void {}
}
