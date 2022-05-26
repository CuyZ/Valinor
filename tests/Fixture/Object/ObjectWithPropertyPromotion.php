<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Fixture\Object;

// @PHP8.0 move to anonymous class
final class ObjectWithPropertyPromotion
{
    public function __construct(
        /** @var non-empty-string */
        public string $someProperty
    ) {
    }
}
