<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

// @PHP8.0 move inside \CuyZ\Valinor\Tests\Integration\Mapping\Object\UnionOfObjectsMappingTest
final class NativeUnionOfObjects
{
    public SomeFooObject|SomeBarObject $object;
}

final class SomeFooObject
{
    public string $foo;
}

final class SomeBarObject
{
    public string $bar;
}
