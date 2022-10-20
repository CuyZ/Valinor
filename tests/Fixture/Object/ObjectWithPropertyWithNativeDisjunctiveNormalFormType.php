<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use Countable;
use DateTime;
use Iterator;

// PHP8.2 move to anonymous class
final class ObjectWithPropertyWithNativeDisjunctiveNormalFormType
{
    public (Countable&Iterator)|(Countable&DateTime) $someProperty;
}
