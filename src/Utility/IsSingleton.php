<?php

declare(strict_types=1);

namespace CuyZ\Valinor\Utility;

/** @internal */
trait IsSingleton
{
    private static self $instance;

    /**
     * PHP8.0
     * @return static
     */
    public static function get(): self
    {
        return self::$instance ??= new static();
    }
}
