<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use CuyZ\Valinor\Tests\Fixture\Attribute\AttributeWithArguments;
use CuyZ\Valinor\Tests\Fixture\Attribute\BasicAttribute;
use CuyZ\Valinor\Tests\Fixture\Attribute\PropertyTargetAttribute;

#[BasicAttribute]
#[AttributeWithArguments('foo', 'bar')]
final class ObjectWithAttributes
{
    #[BasicAttribute]
    #[AttributeWithArguments('foo', 'bar')]
    public bool $property;

    public function __construct(#[PropertyTargetAttribute] public bool $promotedProperty) {}

    #[BasicAttribute]
    #[AttributeWithArguments('foo', 'bar')]
    public function method(#[BasicAttribute] string $parameter): void {}
}
