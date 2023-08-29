<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

class ClassWithMethodWithVariadicParam
{
    /**
     * @param ClassWithMethodWithVariadicParam|float|string ...$values
     */
    public static function method(ClassWithMethodWithVariadicParam|string|float ...$values) : void
    {
    }

}
