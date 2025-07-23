<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Attribute;

use Attribute;
use CuyZ\Valinor\Tests\Fixture\Object\StringableObject;

#[Attribute]
final class AttributeWithArguments
{
    public function __construct(
        public string $foo,
        public string $bar,
        public StringableObject $object = new StringableObject('foo'),
        /** @var array<scalar> */
        public array $array = ['foo' => 'bar'],
    ) {}
}
