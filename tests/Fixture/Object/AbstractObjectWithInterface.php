<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use CuyZ\Valinor\Mapper\Object\Constructor;

abstract class AbstractObjectWithInterface implements \JsonSerializable
{
    #[Constructor]
    public static function of(AbstractObjectWithInterface $value): void {}
}
