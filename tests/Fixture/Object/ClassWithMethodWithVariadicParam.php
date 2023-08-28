<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

class ClassWithMethodWithVariadicParam
{
    /**
     * @param ClassWithMethodWithVariadicParam ...$values
     */
    public static function method(ClassWithMethodWithVariadicParam ...$values) : void
    {
    }

}
