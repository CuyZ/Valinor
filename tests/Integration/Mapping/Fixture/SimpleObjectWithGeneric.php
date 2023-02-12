<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Tests\Integration\Mapping\Fixture;

/**
 * @template T
 */
final class SimpleObjectWithGeneric
{
    /** @var T */
    public $value;
}
