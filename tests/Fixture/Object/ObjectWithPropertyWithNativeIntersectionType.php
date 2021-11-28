<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

use Countable;
use Iterator;

// @PHP8.1 move to anonymous class
final class ObjectWithPropertyWithNativeIntersectionType
{
    /** @var Countable&Iterator<mixed> */
    public Countable&Iterator $someProperty;
}
