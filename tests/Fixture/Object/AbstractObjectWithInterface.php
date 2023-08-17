<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

abstract class AbstractObjectWithInterface implements \JsonSerializable
{
    public static function of(AbstractObjectWithInterface $value): void {}
}
