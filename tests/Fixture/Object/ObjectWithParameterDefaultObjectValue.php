<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

final class ObjectWithParameterDefaultObjectValue
{
    public function method(StringableObject $object = new StringableObject('bar')): StringableObject
    {
        return $object;
    }
}
