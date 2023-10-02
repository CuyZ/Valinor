<?php

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

/**
 * Has the same name of the native class `Error` on purpose.
 */
final class Error
{
    public function __construct(
        public string $message
    ) {}
}
