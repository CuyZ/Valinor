<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

// @PHP8.0 move to anonymous class
final class ObjectWithPropertyWithNativeUnionType
{
    public int|float $someProperty;
}
