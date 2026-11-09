<?php

namespace E\F;

use E\F\E;

/**
 * @template T = string
 */
interface SomeParentInterface
{
    /**
     * @return T
     */
    public function value(): mixed;
}

interface SomeChildInterface extends SomeParentInterface {}
